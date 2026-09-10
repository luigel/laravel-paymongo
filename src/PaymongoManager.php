<?php

declare(strict_types=1);

namespace Luigel\Paymongo;

use Illuminate\Http\Client\Factory;
use Luigel\Paymongo\Client\ClientConfig;
use Luigel\Paymongo\Client\PaymongoClient;
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
use Luigel\Paymongo\Testing\FakesPaymongo;

final class PaymongoManager
{
    use FakesPaymongo;

    private ?PaymongoClient $client = null;

    private ?PaymentIntentService $paymentIntents = null;

    private ?PaymentMethodService $paymentMethods = null;

    private ?PaymentService $payments = null;

    private ?RefundService $refunds = null;

    private ?WebhookService $webhooks = null;

    private ?SourceService $sources = null;

    private ?CheckoutSessionService $checkoutSessions = null;

    private ?LinkService $links = null;

    private ?PaymentLinkService $paymentLinks = null;

    private ?CustomerService $customers = null;

    private ?PlanService $plans = null;

    private ?SubscriptionService $subscriptions = null;

    private ?QrphService $qrph = null;

    private ?PayoutService $payouts = null;

    /**
     * @param  array<string, mixed>  $config  The `paymongo` config array.
     */
    public function __construct(
        private readonly Factory $http,
        private array $config,
    ) {}

    public function client(): PaymongoClient
    {
        return $this->client ??= new PaymongoClient($this->http, ClientConfig::fromArray($this->config));
    }

    public function paymentIntents(): PaymentIntentService
    {
        return $this->paymentIntents ??= new PaymentIntentService($this->client());
    }

    public function paymentMethods(): PaymentMethodService
    {
        return $this->paymentMethods ??= new PaymentMethodService($this->client());
    }

    public function payments(): PaymentService
    {
        return $this->payments ??= new PaymentService($this->client());
    }

    public function refunds(): RefundService
    {
        return $this->refunds ??= new RefundService($this->client());
    }

    public function webhooks(): WebhookService
    {
        return $this->webhooks ??= new WebhookService($this->client());
    }

    /**
     * @deprecated The PayMongo Sources API is deprecated. Use payment intents with e-wallet payment methods instead.
     */
    public function sources(): SourceService
    {
        return $this->sources ??= new SourceService($this->client());
    }

    public function checkoutSessions(): CheckoutSessionService
    {
        return $this->checkoutSessions ??= new CheckoutSessionService($this->client());
    }

    public function links(): LinkService
    {
        return $this->links ??= new LinkService($this->client());
    }

    public function paymentLinks(): PaymentLinkService
    {
        return $this->paymentLinks ??= new PaymentLinkService($this->client());
    }

    public function customers(): CustomerService
    {
        return $this->customers ??= new CustomerService($this->client());
    }

    public function plans(): PlanService
    {
        return $this->plans ??= new PlanService($this->client());
    }

    public function subscriptions(): SubscriptionService
    {
        return $this->subscriptions ??= new SubscriptionService($this->client());
    }

    public function qrph(): QrphService
    {
        return $this->qrph ??= new QrphService($this->client());
    }

    public function payouts(): PayoutService
    {
        return $this->payouts ??= new PayoutService($this->client());
    }

    /**
     * Clone the manager with a different secret key (multi-account use).
     */
    public function withSecretKey(string $secretKey): self
    {
        $manager = clone $this;
        $manager->config['secret_key'] = $secretKey;
        $manager->flushMemoized();

        return $manager;
    }

    /**
     * Forget memoized instances so they are rebuilt from the current config.
     */
    private function flushMemoized(): void
    {
        $this->client = null;
        $this->paymentIntents = null;
        $this->paymentMethods = null;
        $this->payments = null;
        $this->refunds = null;
        $this->webhooks = null;
        $this->sources = null;
        $this->checkoutSessions = null;
        $this->links = null;
        $this->paymentLinks = null;
        $this->customers = null;
        $this->plans = null;
        $this->subscriptions = null;
        $this->qrph = null;
        $this->payouts = null;
    }
}
