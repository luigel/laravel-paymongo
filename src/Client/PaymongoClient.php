<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Client;

use BackedEnum;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Luigel\Paymongo\Exceptions\ConnectionException;
use Luigel\Paymongo\Exceptions\InvalidResponseException;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Throwable;

final readonly class PaymongoClient
{
    use HandlesApiErrors;

    private const USER_AGENT = 'luigel/laravel-paymongo v3 (php '.PHP_VERSION.')';

    /**
     * cURL errors raised before a request leaves the machine: the proxy or host
     * could not be resolved, the connection was refused, or the TLS handshake failed.
     */
    private const UNSENT_CURL_ERRORS = [5, 6, 7, 35];

    public function __construct(
        private Factory $http,
        private ClientConfig $config,
    ) {}

    /**
     * Clone the client with a different secret key (e.g. public-key or multi-account use).
     */
    public function withSecretKey(string $secretKey): self
    {
        return new self($this->http, $this->config->withSecretKey($secretKey));
    }

    public function config(): ClientConfig
    {
        return $this->config;
    }

    /**
     * @param  array<string, mixed>  $query
     *
     * @throws PaymongoException
     */
    public function get(string $path, array $query = []): ApiResponse
    {
        return $this->request('GET', $path, $query === [] ? [] : ['query' => $query], repeatable: true);
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function post(string $path, array $attributes = [], ?string $idempotencyKey = null): ApiResponse
    {
        return $this->create($path, $this->bodyOptions($attributes), $idempotencyKey);
    }

    /**
     * POST to an action endpoint that may return no content.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function postVoid(string $path, array $attributes = [], ?string $idempotencyKey = null): ApiResponse
    {
        return $this->create($path, $this->bodyOptions($attributes), $idempotencyKey, allowEmptyResponse: true);
    }

    /**
     * POST with the body sent as-is, without the `data.attributes` envelope.
     *
     * Newer PayMongo APIs (Payment Links, the v3 QR API) expect flat JSON
     * bodies. Auth, retry, and idempotency semantics match {@see post()};
     * an empty body sends no request body at all.
     *
     * @param  array<string, mixed>  $body
     *
     * @throws PaymongoException
     */
    public function postFlat(string $path, array $body = [], ?string $idempotencyKey = null): ApiResponse
    {
        return $this->create($path, $this->flatBodyOptions($body), $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function put(string $path, array $attributes = []): ApiResponse
    {
        return $this->request('PUT', $path, $this->bodyOptions($attributes), repeatable: false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function patch(string $path, array $attributes = []): ApiResponse
    {
        return $this->request('PATCH', $path, $this->bodyOptions($attributes), repeatable: false);
    }

    /**
     * PATCH with the body sent as-is, without the `data.attributes` envelope.
     *
     * @param  array<string, mixed>  $body
     *
     * @throws PaymongoException
     */
    public function patchFlat(string $path, array $body = []): ApiResponse
    {
        return $this->request('PATCH', $path, $this->flatBodyOptions($body), repeatable: false);
    }

    /**
     * @throws PaymongoException
     */
    public function delete(string $path): ApiResponse
    {
        return $this->request('DELETE', $path, [], repeatable: true, allowEmptyResponse: true);
    }

    /**
     * Send a POST with the caller's idempotency key, or an automatic one.
     *
     * @param  array<string, mixed>  $options
     *
     * @throws PaymongoException
     */
    private function create(string $path, array $options, ?string $idempotencyKey, bool $allowEmptyResponse = false): ApiResponse
    {
        $idempotencyKey ??= $this->config->autoIdempotency ? (string) Str::uuid() : null;

        // PayMongo returns the original result for a repeated key, so a keyed POST is safe to repeat.
        return $this->request('POST', $path, $options, repeatable: $idempotencyKey !== null, idempotencyKey: $idempotencyKey, allowEmptyResponse: $allowEmptyResponse);
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  bool  $repeatable  Whether sending the request twice has the same effect as once, so it
     *                            may be retried after a failure that PayMongo might have acted on.
     *
     * @throws PaymongoException
     */
    private function request(string $method, string $path, array $options, bool $repeatable, ?string $idempotencyKey = null, bool $allowEmptyResponse = false): ApiResponse
    {
        $repeatedAfterAmbiguousFailure = false;

        try {
            $response = $this->pendingRequest($repeatable, $idempotencyKey, $repeatedAfterAmbiguousFailure)->send($method, $path, $options);
        } catch (HttpConnectionException $exception) {
            // Even with retry(..., throw: false), a connection failure on the
            // final attempt (or on a request without retries) is still thrown.
            throw new ConnectionException('Could not connect to PayMongo: '.$exception->getMessage(), $exception);
        }

        if ($method === 'DELETE' && $repeatedAfterAmbiguousFailure && $response->notFound()) {
            // An earlier attempt deleted the resource before its response was lost.
            return new ApiResponse([], $response->status());
        }

        if (! $response->successful()) {
            $this->throwRequestException($response);
        }

        return new ApiResponse($this->decodeBody($response, $allowEmptyResponse), $response->status());
    }

    private function pendingRequest(bool $repeatable, ?string $idempotencyKey, bool &$repeatedAfterAmbiguousFailure): PendingRequest
    {
        $request = $this->http
            ->baseUrl($this->config->baseUrl)
            ->withBasicAuth($this->config->secretKey, '')
            ->timeout($this->config->timeout)
            ->acceptJson()
            ->asJson()
            ->withUserAgent(self::USER_AGENT);

        if ($idempotencyKey !== null) {
            $request = $request->withHeaders(['Idempotency-Key' => $idempotencyKey]);
        }

        return $request->retry(
            $this->config->retries + 1,
            function (int $attempt, Throwable $exception) use (&$repeatedAfterAmbiguousFailure): int {
                $repeatedAfterAmbiguousFailure = $repeatedAfterAmbiguousFailure || ! $this->wasNotProcessed($exception);

                return $this->retryDelay($attempt, $exception);
            },
            fn (Throwable $exception): bool => $this->shouldRetry($exception, $repeatable),
            throw: false,
        );
    }

    /**
     * Any request is retried after a failure PayMongo certainly did not act on. A repeatable
     * request is also retried after a 5xx or a timeout, where PayMongo may have acted.
     */
    private function shouldRetry(Throwable $exception, bool $repeatable): bool
    {
        if ($exception instanceof RequestException) {
            $response = $exception->response;
            $retryAfter = $this->retryAfterMs($exception);

            return ($response->status() === 429 || ($repeatable && $response->serverError()))
                && ($retryAfter === null || $retryAfter <= $this->config->maxRetryDelay);
        }

        return $exception instanceof HttpConnectionException
            && ($repeatable || $this->wasNotProcessed($exception));
    }

    /**
     * Whether the failure proves PayMongo never acted on the request: it rate limited
     * the request, or the connection failed before the request was sent.
     */
    private function wasNotProcessed(Throwable $exception): bool
    {
        if ($exception instanceof RequestException) {
            return $exception->response->status() === 429;
        }

        $previous = $exception->getPrevious();
        $errno = $previous instanceof GuzzleConnectException ? ($previous->getHandlerContext()['errno'] ?? null) : null;

        if (! is_int($errno) && preg_match('/cURL error (\d+)/', $exception->getMessage(), $matches) === 1) {
            $errno = (int) $matches[1];
        }

        return in_array($errno, self::UNSENT_CURL_ERRORS, true);
    }

    /**
     * Wait as long as a `Retry-After` header asks, otherwise back off exponentially
     * with jitter: half the backoff plus a random share of the other half, so
     * clients failing together spread their retries out.
     */
    private function retryDelay(int $attempt, Throwable $exception): int
    {
        $retryAfter = $this->retryAfterMs($exception);

        if ($retryAfter !== null) {
            return $retryAfter;
        }

        $backoff = min($this->config->retryDelay * 2 ** ($attempt - 1), $this->config->maxRetryDelay);
        $floor = intdiv($backoff, 2);

        return $floor + random_int(0, $backoff - $floor);
    }

    /**
     * The wait in milliseconds that the response's `Retry-After` header asks for, if any.
     */
    private function retryAfterMs(Throwable $exception): ?int
    {
        $retryAfter = $exception instanceof RequestException ? $this->parseRetryAfter($exception->response) : null;

        return $retryAfter === null ? null : $retryAfter * 1000;
    }

    /**
     * Wrap non-empty attributes in the PayMongo request envelope.
     * Empty attributes produce no request body at all.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function bodyOptions(array $attributes): array
    {
        if ($attributes === []) {
            return [];
        }

        return ['json' => ['data' => ['attributes' => $this->normalizeEnums($attributes)]]];
    }

    /**
     * Send a flat body verbatim (enum-normalized, no envelope).
     * An empty body produces no request body at all.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function flatBodyOptions(array $body): array
    {
        if ($body === []) {
            return [];
        }

        return ['json' => $this->normalizeEnums($body)];
    }

    /**
     * Recursively replace backed enum instances with their raw values.
     *
     * @param  array<array-key, mixed>  $attributes
     * @return array<array-key, mixed>
     */
    private function normalizeEnums(array $attributes): array
    {
        return array_map(
            fn (mixed $value): mixed => match (true) {
                $value instanceof BackedEnum => $value->value,
                is_array($value) => $this->normalizeEnums($value),
                default => $value,
            },
            $attributes,
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decodeBody(Response $response, bool $allowEmpty): array
    {
        $body = ltrim($response->body());

        if ($allowEmpty && $body === '') {
            return [];
        }

        // Only a body opening with `{` is an object; `[]` or a scalar decodes to a non-object.
        $decoded = str_starts_with($body, '{') ? json_decode($body, true) : null;

        if (! is_array($decoded)) {
            throw new InvalidResponseException(
                'PayMongo returned a successful response with an invalid JSON object.',
                $response->status(),
            );
        }

        return $decoded;
    }
}
