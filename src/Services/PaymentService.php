<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorPage;

final class PaymentService extends AbstractService
{
    /**
     * Charge a chargeable source. This is the last step of the legacy
     * Sources flow; payment intents create their payment for you.
     *
     * @param  array<string, mixed>  $attributes  Supported keys: amount (centavos, min 100), currency, source
     *                                            (`['id' => $sourceId, 'type' => 'source']`), description,
     *                                            statement_descriptor, metadata.
     *
     * @throws PaymongoException
     */
    public function create(array $attributes, ?string $idempotencyKey = null): Payment
    {
        return $this->one($this->client->post('/payments', $attributes, $idempotencyKey), Payment::class);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): Payment
    {
        return $this->one($this->client->get("/payments/{$id}"), Payment::class);
    }

    /**
     * @param  array<string, mixed>  $params  Supported keys: limit, before, after.
     * @return CursorPage<Payment>
     *
     * @throws PaymongoException
     */
    public function list(array $params = []): CursorPage
    {
        return $this->page(Payment::class, '/payments', $params);
    }
}
