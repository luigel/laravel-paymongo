<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use Luigel\Paymongo\Events\WebhookReceived;

/**
 * Persists every inbound PayMongo webhook so the E2E test process (a separate
 * PHP process from `php artisan serve`) can observe deliveries through the
 * shared SQLite database.
 */
class RecordWebhookDelivery
{
    public function handle(WebhookReceived $event): void
    {
        $webhookEvent = $event->event;

        // $webhookEvent->data is the embedded resource: {id, type, attributes}.
        $resourceType = $webhookEvent->data['type'] ?? null;

        $payload = json_encode($webhookEvent->raw, JSON_UNESCAPED_SLASHES);

        DB::table('webhook_deliveries')->insertOrIgnore([
            'event_id'      => $webhookEvent->id !== '' ? $webhookEvent->id : 'evt_unknown_'.bin2hex(random_bytes(8)),
            'event_type'    => $webhookEvent->type,
            'resource_type' => is_string($resourceType) ? $resourceType : null,
            'resource_id'   => $webhookEvent->resourceId(),
            'payload'       => is_string($payload) ? $payload : '{}',
            'received_at'   => now()->toDateTimeString(),
        ]);
    }
}
