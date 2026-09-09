<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

/**
 * Thrown when PayMongo rejects the API key (HTTP 401).
 */
final class AuthenticationException extends PaymongoException {}
