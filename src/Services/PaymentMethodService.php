<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\PaymentMethod;
use Luigel\Paymongo\Exceptions\PaymongoException;

final class PaymentMethodService extends AbstractService
{
    /**
     * @param array<string, mixed> $attributes
     *
     * @throws PaymongoException
     */
    public function create(array $attributes): PaymentMethod
    {
        return $this->one($this->client->post('/payment_methods', $attributes), PaymentMethod::class);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): PaymentMethod
    {
        return $this->one($this->client->get("/payment_methods/{$id}"), PaymentMethod::class);
    }
}
