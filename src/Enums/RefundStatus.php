<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Lifecycle states of a refund.
 */
enum RefundStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
