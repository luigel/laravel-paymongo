<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Subscription;
use Luigel\Paymongo\Enums\CancellationReason;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorPage;

final class SubscriptionService extends AbstractService
{
    /**
     * Subscribe a customer to a plan.
     *
     * @throws PaymongoException
     */
    public function create(string $customerId, string $planId): Subscription
    {
        return $this->one(
            $this->client->post('/subscriptions', ['customer_id' => $customerId, 'plan_id' => $planId]),
            Subscription::class,
        );
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): Subscription
    {
        return $this->one($this->client->get("/subscriptions/{$id}"), Subscription::class);
    }

    /**
     * @param array<string, mixed> $params Supported keys: limit, before, after.
     *
     * @throws PaymongoException
     *
     * @return CursorPage<Subscription>
     */
    public function list(array $params = []): CursorPage
    {
        return $this->page(Subscription::class, '/subscriptions', $params);
    }

    /**
     * Cancel the subscription. PayMongo requires a cancellation reason.
     *
     * @throws PaymongoException
     */
    public function cancel(string $id, CancellationReason|string $reason): Subscription
    {
        return $this->one(
            $this->client->post("/subscriptions/{$id}/cancel", ['cancellation_reason' => $reason]),
            Subscription::class,
        );
    }

    /**
     * Move the subscription onto a different plan.
     *
     * @throws PaymongoException
     */
    public function changePlan(string $id, string $planId): Subscription
    {
        return $this->one(
            $this->client->put("/subscriptions/{$id}/plan", ['plan_id' => $planId]),
            Subscription::class,
        );
    }

    /**
     * Change the payment method future cycles are charged against.
     *
     * @param string|null $redirectUrl Where the customer lands after authorizing the new method.
     *
     * @throws PaymongoException
     */
    public function changePaymentMethod(string $id, string $paymentMethodId, ?string $redirectUrl = null): Subscription
    {
        $attributes = ['payment_method_id' => $paymentMethodId];

        if ($redirectUrl !== null) {
            $attributes['redirect_url'] = $redirectUrl;
        }

        return $this->one(
            $this->client->put("/subscriptions/{$id}/payment_method", $attributes),
            Subscription::class,
        );
    }

    /**
     * Trigger an immediate billing cycle on a test-mode subscription.
     *
     * @throws PaymongoException
     */
    public function triggerTestCycle(string $id): void
    {
        $this->client->post("/subscriptions/{$id}/test_cycle");
    }
}
