<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Client;

use Illuminate\Http\Client\Response;
use Luigel\Paymongo\Data\ApiError;
use Luigel\Paymongo\Exceptions\AuthenticationException;
use Luigel\Paymongo\Exceptions\InvalidRequestException;
use Luigel\Paymongo\Exceptions\PaymentDeclinedException;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Exceptions\RateLimitException;
use Luigel\Paymongo\Exceptions\ResourceNotFoundException;
use Luigel\Paymongo\Exceptions\ServerException;

/**
 * The single place where non-2xx PayMongo responses become package exceptions.
 */
trait HandlesApiErrors
{
    /**
     * @throws PaymongoException
     */
    private function throwRequestException(Response $response): never
    {
        $status = $response->status();
        $errors = $this->parseApiErrors($response);
        $message = $errors[0]->detail ?? sprintf('PayMongo request failed with status %d.', $status);

        throw match (true) {
            $status === 401 => new AuthenticationException($message, $status, $errors),
            $status === 402 => new PaymentDeclinedException($message, $status, $errors),
            $status === 404 => new ResourceNotFoundException($message, $status, $errors),
            $status === 429 => new RateLimitException($message, $status, $errors, $this->parseRetryAfter($response)),
            $status >= 500  => new ServerException($message, $status, $errors),
            default         => new InvalidRequestException($message, $status, $errors),
        };
    }

    /**
     * Parse the PayMongo `errors[]` payload; when the body is not the expected
     * JSON shape, fall back to the raw body as a single error detail.
     *
     * @return list<ApiError>
     */
    private function parseApiErrors(Response $response): array
    {
        $errors = $response->json('errors');

        if (is_array($errors)) {
            $parsed = [];

            foreach ($errors as $error) {
                if (is_array($error)) {
                    $parsed[] = ApiError::fromArray($error);
                }
            }

            return $parsed;
        }

        $body = trim($response->body());

        return $body === '' ? [] : [new ApiError(detail: $body)];
    }

    private function parseRetryAfter(Response $response): ?int
    {
        $header = $response->header('Retry-After');

        return is_numeric($header) ? (int) $header : null;
    }
}
