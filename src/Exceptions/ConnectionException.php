<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

use Throwable;

/**
 * Thrown when PayMongo cannot be reached at all (DNS failure, timeout, ...).
 */
final class ConnectionException extends PaymongoException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, null, [], $previous);
    }
}
