<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Lifecycle states of a checkout session.
 */
enum CheckoutSessionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
}
