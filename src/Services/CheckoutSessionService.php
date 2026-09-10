<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\CheckoutSession;
use Luigel\Paymongo\Exceptions\PaymongoException;

final class CheckoutSessionService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function create(array $attributes, ?string $idempotencyKey = null): CheckoutSession
    {
        return $this->one($this->client->post('/checkout_sessions', $attributes, $idempotencyKey), CheckoutSession::class);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): CheckoutSession
    {
        return $this->one($this->client->get("/checkout_sessions/{$id}"), CheckoutSession::class);
    }

    /**
     * Expire an active checkout session so it can no longer be paid.
     *
     * @throws PaymongoException
     */
    public function expire(string $id): CheckoutSession
    {
        return $this->one($this->client->post("/checkout_sessions/{$id}/expire"), CheckoutSession::class);
    }
}
