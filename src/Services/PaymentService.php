<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorPage;

final class PaymentService extends AbstractService
{
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
