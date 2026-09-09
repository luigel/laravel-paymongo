<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Currencies PayMongo can process. Philippine peso only.
 */
enum Currency: string
{
    case PHP = 'PHP';
}
