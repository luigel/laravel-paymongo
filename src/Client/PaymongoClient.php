<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Client;

use BackedEnum;
use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Luigel\Paymongo\Exceptions\ConnectionException;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Throwable;

final readonly class PaymongoClient
{
    use HandlesApiErrors;

    private const USER_AGENT = 'luigel/laravel-paymongo v3 (php '.PHP_VERSION.')';

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
        return $this->request('GET', $path, $query === [] ? [] : ['query' => $query], retryable: true);
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function post(string $path, array $attributes = [], ?string $idempotencyKey = null): ApiResponse
    {
        $idempotencyKey ??= $this->config->autoIdempotency ? (string) Str::uuid() : null;

        return $this->request(
            'POST',
            $path,
            $this->bodyOptions($attributes),
            retryable: $idempotencyKey !== null,
            idempotencyKey: $idempotencyKey,
        );
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
        $idempotencyKey ??= $this->config->autoIdempotency ? (string) Str::uuid() : null;

        return $this->request(
            'POST',
            $path,
            $this->flatBodyOptions($body),
            retryable: $idempotencyKey !== null,
            idempotencyKey: $idempotencyKey,
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function put(string $path, array $attributes = []): ApiResponse
    {
        return $this->request('PUT', $path, $this->bodyOptions($attributes), retryable: false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function patch(string $path, array $attributes = []): ApiResponse
    {
        return $this->request('PATCH', $path, $this->bodyOptions($attributes), retryable: false);
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
        return $this->request('PATCH', $path, $this->flatBodyOptions($body), retryable: false);
    }

    /**
     * @throws PaymongoException
     */
    public function delete(string $path): ApiResponse
    {
        return $this->request('DELETE', $path, [], retryable: true);
    }

    /**
     * @param  array<string, mixed>  $options
     *
     * @throws PaymongoException
     */
    private function request(string $method, string $path, array $options, bool $retryable, ?string $idempotencyKey = null): ApiResponse
    {
        try {
            $response = $this->pendingRequest($retryable, $idempotencyKey)->send($method, $path, $options);
        } catch (HttpConnectionException $exception) {
            // Even with retry(..., throw: false), a connection failure on the
            // final attempt (or on a request without retries) is still thrown.
            throw new ConnectionException('Could not connect to PayMongo: '.$exception->getMessage(), $exception);
        }

        if (! $response->successful()) {
            $this->throwRequestException($response);
        }

        return new ApiResponse($this->decodeBody($response), $response->status());
    }

    private function pendingRequest(bool $retryable, ?string $idempotencyKey): PendingRequest
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

        if ($retryable) {
            return $request->retry(
                $this->config->retries,
                $this->config->retryDelay,
                fn (Throwable $exception): bool => $this->shouldRetry($exception),
                throw: false,
            );
        }

        return $request;
    }

    private function shouldRetry(Throwable $exception): bool
    {
        if ($exception instanceof HttpConnectionException) {
            return true;
        }

        return $exception instanceof RequestException
            && ($exception->response->status() === 429 || $exception->response->serverError());
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
    private function decodeBody(Response $response): array
    {
        $decoded = $response->json();

        return is_array($decoded) ? $decoded : [];
    }
}
