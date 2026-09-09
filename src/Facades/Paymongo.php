<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Facades;

use Illuminate\Support\Facades\Facade;
use Luigel\Paymongo\PaymongoManager;

/**
 * @method static \Luigel\Paymongo\Client\PaymongoClient client()
 * @method static \Luigel\Paymongo\PaymongoManager withSecretKey(string $secretKey)
 *
 * @see PaymongoManager
 */
final class Paymongo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PaymongoManager::class;
    }
}
