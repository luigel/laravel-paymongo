<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Lifecycle states of a dispute (chargeback), as PayMongo's Disputes guide
 * and dispute webhook events name them.
 */
enum DisputeStatus: string
{
    case UnderReview = 'under_review';
    case Won = 'won';
    case Lost = 'lost';
    case Expired = 'expired';
}
