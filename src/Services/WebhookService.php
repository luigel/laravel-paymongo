<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Webhook;
use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorPage;

final class WebhookService extends AbstractService
{
    /**
     * @param  list<WebhookEventType|string>  $events
     *
     * @throws PaymongoException
     */
    public function create(string $url, array $events): Webhook
    {
        return $this->one($this->client->post('/webhooks', ['url' => $url, 'events' => $events]), Webhook::class);
    }

    /**
     * @param  array<string, mixed>  $params  Supported keys: limit, before, after, url.
     * @return CursorPage<Webhook>
     *
     * @throws PaymongoException
     */
    public function list(array $params = []): CursorPage
    {
        return $this->page(Webhook::class, '/webhooks', $params);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): Webhook
    {
        return $this->one($this->client->get("/webhooks/{$id}"), Webhook::class);
    }

    /**
     * @param  array<string, mixed>  $attributes  Supported keys: url, events.
     *
     * @throws PaymongoException
     */
    public function update(string $id, array $attributes): Webhook
    {
        return $this->one($this->client->put("/webhooks/{$id}", $attributes), Webhook::class);
    }

    /**
     * @throws PaymongoException
     */
    public function enable(string $id): Webhook
    {
        return $this->one($this->client->post("/webhooks/{$id}/enable"), Webhook::class);
    }

    /**
     * @throws PaymongoException
     */
    public function disable(string $id): Webhook
    {
        return $this->one($this->client->post("/webhooks/{$id}/disable"), Webhook::class);
    }

    /**
     * Delete the endpoint. The client throws on failure, so reaching the
     * return value always means the deletion succeeded.
     *
     * @throws PaymongoException
     */
    public function delete(string $id): bool
    {
        $this->client->delete("/webhooks/{$id}");

        return true;
    }
}
