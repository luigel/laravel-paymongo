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
 * @method static \Luigel\Paymongo\Services\CheckoutSessionService checkoutSessions()
 * @method static \Luigel\Paymongo\Services\LinkService links()
 * @method static \Luigel\Paymongo\Services\CustomerService customers()
 * @method static \Luigel\Paymongo\Services\PlanService plans()
 * @method static \Luigel\Paymongo\Services\SubscriptionService subscriptions()
 * @method static \Luigel\Paymongo\PaymongoManager withSecretKey(string $secretKey)
 * @method static void fake(array<string, mixed> $stubs = [])
 * @method static void assertSent(callable $callback)
 * @method static void assertNothingSent()
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
