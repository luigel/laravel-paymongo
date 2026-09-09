<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Lifecycle states of a payout.
 */
enum PayoutStatus: string
{
    case Pending = 'pending';
    case OnHold = 'on_hold';
    case InTransit = 'in_transit';
    case Deposited = 'deposited';
    case Returned = 'returned';
    case Cancelled = 'cancelled';
}
