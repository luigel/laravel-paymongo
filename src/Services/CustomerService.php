<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Customer;
use Luigel\Paymongo\Data\CustomerPaymentMethod;
use Luigel\Paymongo\Exceptions\PaymongoException;

final class CustomerService extends AbstractService
{
    /**
     * @param array<string, mixed> $attributes Supported keys: first_name, last_name, phone, email, default_device.
     *
     * @throws PaymongoException
     */
    public function create(array $attributes): Customer
    {
        return $this->one($this->client->post('/customers', $attributes), Customer::class);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): Customer
    {
        return $this->one($this->client->get("/customers/{$id}"), Customer::class);
    }

    /**
     * @param array<string, mixed> $attributes Same keys as {@see create()}.
     *
     * @throws PaymongoException
     */
    public function update(string $id, array $attributes): Customer
    {
        return $this->one($this->client->patch("/customers/{$id}", $attributes), Customer::class);
    }

    /**
     * Delete the customer. The client throws on failure, so reaching the
     * return value always means the deletion succeeded.
     *
     * @throws PaymongoException
     */
    public function delete(string $id): bool
    {
        $this->client->delete("/customers/{$id}");

        return true;
    }

    /**
     * The payment methods saved against the customer.
     *
     * @throws PaymongoException
     *
     * @return list<CustomerPaymentMethod>
     */
    public function paymentMethods(string $customerId): array
    {
        return $this->many(
            $this->client->get("/customers/{$customerId}/payment_methods"),
            CustomerPaymentMethod::class,
        );
    }

    /**
     * Remove a saved payment method from the customer.
     *
     * @throws PaymongoException
     */
    public function deletePaymentMethod(string $customerId, string $paymentMethodId): bool
    {
        $this->client->delete("/customers/{$customerId}/payment_methods/{$paymentMethodId}");

        return true;
    }
}
