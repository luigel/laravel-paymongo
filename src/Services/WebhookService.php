<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Webhook;
use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\Exceptions\PaymongoException;

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
     * @return list<Webhook>
     *
     * @throws PaymongoException
     */
    public function list(): array
    {
        return $this->many($this->client->get('/webhooks'), Webhook::class);
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
}
