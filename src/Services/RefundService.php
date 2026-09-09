<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Refund;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorPage;

final class RefundService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function create(array $attributes, ?string $idempotencyKey = null): Refund
    {
        return $this->one($this->client->post('/refunds', $attributes, $idempotencyKey), Refund::class);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): Refund
    {
        return $this->one($this->client->get("/refunds/{$id}"), Refund::class);
    }

    /**
     * @param  array<string, mixed>  $params  Supported keys: limit, before, after, payment_id.
     * @return CursorPage<Refund>
     *
     * @throws PaymongoException
     */
    public function list(array $params = []): CursorPage
    {
        return $this->page(Refund::class, '/refunds', $params);
    }
}
