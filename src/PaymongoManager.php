<?php

declare(strict_types=1);

namespace Luigel\Paymongo;

use Illuminate\Http\Client\Factory;
use Luigel\Paymongo\Client\ClientConfig;
use Luigel\Paymongo\Client\PaymongoClient;
use Luigel\Paymongo\Services\PaymentIntentService;
use Luigel\Paymongo\Services\PaymentMethodService;
use Luigel\Paymongo\Services\PaymentService;
use Luigel\Paymongo\Services\RefundService;
use Luigel\Paymongo\Services\SourceService;
use Luigel\Paymongo\Services\WebhookService;

final class PaymongoManager
{
    private ?PaymongoClient $client = null;

    private ?PaymentIntentService $paymentIntents = null;

    private ?PaymentMethodService $paymentMethods = null;

    private ?PaymentService $payments = null;

    private ?RefundService $refunds = null;

    private ?WebhookService $webhooks = null;

    private ?SourceService $sources = null;

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
    }
}
