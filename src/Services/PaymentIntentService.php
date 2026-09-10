<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\PaymentIntent;
use Luigel\Paymongo\Exceptions\AuthenticationException;
use Luigel\Paymongo\Exceptions\PaymongoException;

final class PaymentIntentService extends AbstractService
{
    /**
     * @param array<string, mixed> $attributes
     *
     * @throws PaymongoException
     */
    public function create(array $attributes, ?string $idempotencyKey = null): PaymentIntent
    {
        return $this->one($this->client->post('/payment_intents', $attributes, $idempotencyKey), PaymentIntent::class);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): PaymentIntent
    {
        return $this->one($this->client->get("/payment_intents/{$id}"), PaymentIntent::class);
    }

    /**
     * Retrieve a payment intent client-side: authenticates with the configured
     * public key and passes the intent's client key as a query parameter.
     *
     * @throws PaymongoException
     */
    public function retrieveUsingClientKey(string $id, string $clientKey): PaymentIntent
    {
        $publicKey = $this->client->config()->publicKey;

        if ($publicKey === null || $publicKey === '') {
            throw new AuthenticationException(
                'No PayMongo public key is configured. Set paymongo.public_key (PAYMONGO_PUBLIC_KEY) to retrieve payment intents with a client key.'
            );
        }

        return $this->one(
            $this->client->withSecretKey($publicKey)->get("/payment_intents/{$id}", ['client_key' => $clientKey]),
            PaymentIntent::class,
        );
    }

    /**
     * Attach a payment method to the payment intent.
     *
     * @param string|null $returnUrl Required by PayMongo for e-wallet, DOB and BillEase payments.
     *
     * @throws PaymongoException
     */
    public function attach(string $id, string $paymentMethodId, ?string $returnUrl = null, ?string $clientKey = null): PaymentIntent
    {
        $attributes = ['payment_method' => $paymentMethodId];

        if ($returnUrl !== null) {
            $attributes['return_url'] = $returnUrl;
        }

        if ($clientKey !== null) {
            $attributes['client_key'] = $clientKey;
        }

        return $this->one($this->client->post("/payment_intents/{$id}/attach", $attributes), PaymentIntent::class);
    }

    /**
     * Capture a manually-captured intent; omit the amount for a full capture.
     *
     * @throws PaymongoException
     */
    public function capture(string $id, ?int $amount = null): PaymentIntent
    {
        return $this->one(
            $this->client->post("/payment_intents/{$id}/capture", $amount === null ? [] : ['amount' => $amount]),
            PaymentIntent::class,
        );
    }

    /**
     * @throws PaymongoException
     */
    public function cancel(string $id): PaymentIntent
    {
        return $this->one($this->client->post("/payment_intents/{$id}/cancel"), PaymentIntent::class);
    }
}
