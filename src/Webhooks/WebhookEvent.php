<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Webhooks;

use Carbon\CarbonImmutable;
use Luigel\Paymongo\Enums\WebhookEventType;

/**
 * A parsed inbound webhook event.
 *
 * Built from the event envelope PayMongo posts to your endpoint:
 * `{"data": {"id", "type": "event", "attributes": {"type", "livemode", "data": {...resource}, "created_at"}}}`.
 */
final class WebhookEvent
{
    /**
     * @param  string  $id  The event id (`evt_...`).
     * @param  string  $type  The dotted event name, e.g. `payment.paid`.
     * @param  array<string, mixed>  $data  The full embedded resource (`{id, type, attributes}`).
     * @param  array<array-key, mixed>  $raw  The unmodified request payload.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly bool $livemode,
        public readonly array $data,
        public readonly ?CarbonImmutable $timestamp,
        public readonly array $raw,
    ) {}

    /**
     * Build the event from a raw webhook payload, tolerating missing keys.
     *
     * @param  array<array-key, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $data = $payload['data'] ?? null;
        $data = is_array($data) ? $data : [];

        $attributes = $data['attributes'] ?? null;
        $attributes = is_array($attributes) ? $attributes : [];

        $id = $data['id'] ?? null;
        $type = $attributes['type'] ?? null;
        $createdAt = $attributes['created_at'] ?? null;

        $resource = $attributes['data'] ?? null;
        /** @var array<string, mixed> $resource */
        $resource = is_array($resource) ? $resource : [];

        return new self(
            id: is_string($id) ? $id : '',
            type: is_string($type) ? $type : '',
            livemode: (bool) ($attributes['livemode'] ?? false),
            data: $resource,
            timestamp: is_int($createdAt) ? CarbonImmutable::createFromTimestamp($createdAt) : null,
            raw: $payload,
        );
    }

    /**
     * The event name as an enum; null for names this package does not know.
     */
    public function eventType(): ?WebhookEventType
    {
        return WebhookEventType::tryFrom($this->type);
    }

    /**
     * The id of the resource embedded in the event (e.g. `pay_...`).
     */
    public function resourceId(): ?string
    {
        $id = $this->data['id'] ?? null;

        return is_string($id) ? $id : null;
    }

    /**
     * Read an attribute of the embedded resource using dot notation.
     */
    public function resourceAttribute(string $key, mixed $default = null): mixed
    {
        return data_get($this->data['attributes'] ?? [], $key, $default);
    }
}
