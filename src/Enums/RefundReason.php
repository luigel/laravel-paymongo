<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Reasons PayMongo accepts when creating a refund.
 */
enum RefundReason: string
{
    case Duplicate = 'duplicate';
    case Fraudulent = 'fraudulent';
    case Others = 'others';
}
