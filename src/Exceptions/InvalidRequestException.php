<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

/**
 * Thrown for client errors not covered by a more specific exception
 * (HTTP 400, 403, 422, and any other unmapped 4xx).
 */
final class InvalidRequestException extends PaymongoException
{
}
