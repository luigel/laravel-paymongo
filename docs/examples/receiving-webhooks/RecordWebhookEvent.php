<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Luigel\Paymongo\Events\WebhookReceived;

class RecordWebhookEvent
{
    public function handle(WebhookReceived $event): void
    {
        $webhookEvent = $event->event;

        Log::info("PayMongo event {$webhookEvent->type}", [
            'event_id' => $webhookEvent->id,                    // "evt_..."
            'known' => $webhookEvent->eventType() !== null,     // ?WebhookEventType
            'resource_id' => $webhookEvent->resourceId(),
            'livemode' => $webhookEvent->livemode,
        ]);
    }
}
