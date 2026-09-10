<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

/**
 * Thrown when a payment is declined by the processor (HTTP 402).
 */
final class PaymentDeclinedException extends PaymongoException
{
}
