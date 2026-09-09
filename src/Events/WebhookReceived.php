<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

use Luigel\Paymongo\Webhooks\EventMap;
use Luigel\Paymongo\Webhooks\WebhookEvent;

/**
 * Dispatched for every verified inbound webhook, whatever its event name.
 *
 * A typed subclass is dispatched alongside it when the event name has one
 * (see {@see EventMap}).
 */
class WebhookReceived
{
    public function __construct(public readonly WebhookEvent $event) {}
}
