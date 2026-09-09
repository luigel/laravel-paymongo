<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Management states of a payment link (`/payment_links` API).
 *
 * Unlike the legacy `/links` API this is not a payment state: a link is
 * either active or archived.
 */
enum PaymentLinkStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
