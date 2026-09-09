<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * How a payment intent captures an authorized amount.
 */
enum CaptureType: string
{
    case Automatic = 'automatic';
    case Manual = 'manual';
}
