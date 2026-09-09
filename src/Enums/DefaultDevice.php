<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Devices PayMongo can use to reach a customer by default.
 */
enum DefaultDevice: string
{
    case Phone = 'phone';
    case Email = 'email';
}
