<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

/**
 * Thrown when PayMongo fails with a server error (HTTP 5xx).
 */
final class ServerException extends PaymongoException {}
