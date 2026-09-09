<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Lifecycle states of an MPM QR code (v3 QR API).
 */
enum QrStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
}
