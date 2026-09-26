<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

/**
 * Thrown when PayMongo returns a successful response that cannot be mapped to the expected payload.
 */
final class InvalidResponseException extends PaymongoException {}
