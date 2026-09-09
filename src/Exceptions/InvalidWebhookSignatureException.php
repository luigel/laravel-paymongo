<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

/**
 * Thrown when an inbound webhook request fails Paymongo-Signature verification.
 */
final class InvalidWebhookSignatureException extends PaymongoException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
