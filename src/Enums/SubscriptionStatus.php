<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Lifecycle states of a subscription.
 */
enum SubscriptionStatus: string
{
    case Incomplete = 'incomplete';
    case IncompleteCancelled = 'incomplete_cancelled';
    case Active = 'active';
    case PastDue = 'past_due';
    case Unpaid = 'unpaid';
    case Cancelled = 'cancelled';
}
