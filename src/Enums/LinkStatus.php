<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Lifecycle states of a payment link.
 */
enum LinkStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Archived = 'archived';
}
