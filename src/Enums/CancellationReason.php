<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Reasons PayMongo accepts when cancelling a subscription.
 */
enum CancellationReason: string
{
    case TooExpensive = 'too_expensive';
    case MissingFeatures = 'missing_features';
    case SwitchedService = 'switched_service';
    case Unused = 'unused';
    case Other = 'other';
}
