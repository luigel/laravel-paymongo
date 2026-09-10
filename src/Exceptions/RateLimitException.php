<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

use Luigel\Paymongo\Data\ApiError;
use Throwable;

/**
 * Thrown when PayMongo rate limits the request (HTTP 429).
 */
final class RateLimitException extends PaymongoException
{
    /**
     * @param list<ApiError> $errors
     * @param ?int           $retryAfter Seconds from the `Retry-After` header, when present.
     */
    public function __construct(
        string $message,
        ?int $status = null,
        array $errors = [],
        public readonly ?int $retryAfter = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $errors, $previous);
    }
}
