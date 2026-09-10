<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Plan;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorPage;

final class PlanService extends AbstractService
{
    /**
     * @param array<string, mixed> $attributes Supported keys: name, description, amount, currency,
     *                                         interval, interval_count, plan_type, cycle_count, metadata.
     *
     * @throws PaymongoException
     */
    public function create(array $attributes): Plan
    {
        return $this->one($this->client->post('/subscriptions/plans', $attributes), Plan::class);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): Plan
    {
        return $this->one($this->client->get("/subscriptions/plans/{$id}"), Plan::class);
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @throws PaymongoException
     */
    public function update(string $id, array $attributes): Plan
    {
        return $this->one($this->client->patch("/subscriptions/plans/{$id}", $attributes), Plan::class);
    }

    /**
     * @param array<string, mixed> $params Supported keys: limit, before, after.
     *
     * @throws PaymongoException
     *
     * @return CursorPage<Plan>
     */
    public function list(array $params = []): CursorPage
    {
        return $this->page(Plan::class, '/subscriptions/plans', $params);
    }
}
