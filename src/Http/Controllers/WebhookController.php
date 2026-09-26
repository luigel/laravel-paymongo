<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Http\Controllers;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Luigel\Paymongo\Events\WebhookReceived;
use Luigel\Paymongo\Webhooks\EventMap;
use Luigel\Paymongo\Webhooks\WebhookEvent;
use RuntimeException;

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

        if (! (bool) config('paymongo.webhooks.dedupe.enabled', true)) {
            $this->dispatch($event);

            return response()->json(['received' => true]);
        }

        $store = config('paymongo.webhooks.dedupe.store');
        $cache = Cache::store(is_string($store) ? $store : null);

        $locks = $cache instanceof Repository ? $cache->getStore() : null;

        if (! $locks instanceof LockProvider) {
            throw new RuntimeException('The PayMongo webhook dedupe cache store must support atomic locks.');
        }

        $key = 'paymongo:webhook:'.$event->id;

        $processed = $locks->lock($key.':processing', 60)->get(function () use ($cache, $key, $event): true {
            if (! $cache->has($key)) {
                $this->dispatch($event);
                $cache->put($key, true, $this->dedupeTtl());
            }

            return true;
        });

        return $processed === true
            ? response()->json(['received' => true])
            : response()->json(['received' => false], 503);
    }

    private function dedupeTtl(): int
    {
        $ttl = config('paymongo.webhooks.dedupe.ttl', 86400);

        return is_numeric($ttl) ? (int) $ttl : 86400;
    }

    private function dispatch(WebhookEvent $event): void
    {
        event(new WebhookReceived($event));

        $eventClass = EventMap::eventClassFor($event->type);

        if ($eventClass !== null) {
            event(new $eventClass($event));
        }
    }
}
