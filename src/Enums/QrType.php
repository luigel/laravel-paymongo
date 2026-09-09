<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Kinds of MPM QR codes (v3 QR API): dynamic carries a fixed amount,
 * static lets the scanner enter one.
 */
enum QrType: string
{
    case Dynamic = 'dynamic';
    case Static = 'static';
}
