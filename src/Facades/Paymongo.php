<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Facades;

use Illuminate\Support\Facades\Facade;
use Luigel\Paymongo\PaymongoManager;

/**
 * @method static \Luigel\Paymongo\Client\PaymongoClient client()
 * @method static \Luigel\Paymongo\Services\PaymentIntentService paymentIntents()
 * @method static \Luigel\Paymongo\Services\PaymentMethodService paymentMethods()
 * @method static \Luigel\Paymongo\Services\PaymentService payments()
 * @method static \Luigel\Paymongo\Services\RefundService refunds()
 * @method static \Luigel\Paymongo\Services\WebhookService webhooks()
 * @method static \Luigel\Paymongo\Services\SourceService sources()
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
