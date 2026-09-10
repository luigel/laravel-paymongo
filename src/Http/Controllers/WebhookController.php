<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Luigel\Paymongo\Events\WebhookReceived;
use Luigel\Paymongo\Webhooks\EventMap;
use Luigel\Paymongo\Webhooks\WebhookEvent;

/**
 * Receives verified webhook requests and turns them into Laravel events.
 *
 * Always dispatches the generic {@see WebhookReceived} event, plus the typed
 * subclass mapped by {@see EventMap} when the event name has one. Duplicate
 * deliveries (PayMongo retries) are suppressed through the cache when
 * `paymongo.webhooks.dedupe.enabled` is on.
 */
final class WebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        $event = WebhookEvent::fromArray($request->all());

        if ($this->isDuplicate($event)) {
            return response()->json(['received' => true]);
        }

        event(new WebhookReceived($event));

        $eventClass = EventMap::eventClassFor($event->type);

        if ($eventClass !== null) {
            event(new $eventClass($event));
        }

        return response()->json(['received' => true]);
    }

    /**
     * Remember the event id; a second delivery of the same id is a duplicate.
     */
    private function isDuplicate(WebhookEvent $event): bool
    {
        if (!(bool) config('paymongo.webhooks.dedupe.enabled', true)) {
            return false;
        }

        $store = config('paymongo.webhooks.dedupe.store');
        $ttl = config('paymongo.webhooks.dedupe.ttl', 86400);

        return !Cache::store(is_string($store) ? $store : null)->add(
            'paymongo:webhook:'.$event->id,
            true,
            is_numeric($ttl) ? (int) $ttl : 86400,
        );
    }
}
