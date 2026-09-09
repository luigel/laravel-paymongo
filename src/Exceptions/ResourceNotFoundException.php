<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

/**
 * Thrown when the requested resource does not exist (HTTP 404).
 */
final class ResourceNotFoundException extends PaymongoException {}
