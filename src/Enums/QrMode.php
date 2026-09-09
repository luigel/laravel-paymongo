<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Transfer modes of an MPM QR code (v3 QR API).
 */
enum QrMode: string
{
    case P2p = 'p2p';
    case P2b = 'p2b';
    case P2m = 'p2m';
    case P2micro = 'p2micro';
}
