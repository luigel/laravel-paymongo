<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Facades;

use Illuminate\Support\Facades\Facade;
use Luigel\Paymongo\Client\PaymongoClient;
use Luigel\Paymongo\PaymongoManager;
use Luigel\Paymongo\Services\CheckoutSessionService;
use Luigel\Paymongo\Services\CustomerService;
use Luigel\Paymongo\Services\LinkService;
use Luigel\Paymongo\Services\PaymentIntentService;
use Luigel\Paymongo\Services\PaymentLinkService;
use Luigel\Paymongo\Services\PaymentMethodService;
use Luigel\Paymongo\Services\PaymentService;
use Luigel\Paymongo\Services\PayoutService;
use Luigel\Paymongo\Services\PlanService;
use Luigel\Paymongo\Services\QrphService;
use Luigel\Paymongo\Services\RefundService;
use Luigel\Paymongo\Services\SourceService;
use Luigel\Paymongo\Services\SubscriptionService;
use Luigel\Paymongo\Services\WebhookService;

/**
 * @method static PaymongoClient         client()
 * @method static PaymentIntentService   paymentIntents()
 * @method static PaymentMethodService   paymentMethods()
 * @method static PaymentService         payments()
 * @method static RefundService          refunds()
 * @method static WebhookService         webhooks()
 * @method static SourceService          sources()
 * @method static CheckoutSessionService checkoutSessions()
 * @method static LinkService            links()
 * @method static PaymentLinkService     paymentLinks()
 * @method static CustomerService        customers()
 * @method static PlanService            plans()
 * @method static SubscriptionService    subscriptions()
 * @method static QrphService            qrph()
 * @method static PayoutService          payouts()
 * @method static PaymongoManager        withSecretKey(string $secretKey)
 * @method static void                   fake(array<string, mixed> $stubs = [])
 * @method static void                   assertSent(callable $callback)
 * @method static void                   assertNothingSent()
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
