<?php

use Luigel\Paymongo\Enums\WebhookStatus;
use Luigel\Paymongo\Facades\Paymongo;

foreach (Paymongo::webhooks()->list() as $webhook) { // list<Webhook>, every endpoint at once
    $webhook->url;
    $webhook->events;                              // list<string>
    $webhook->status === WebhookStatus::Enabled;   // ?WebhookStatus
}

$webhook = Paymongo::webhooks()->retrieve('hook_9VrvpRkkYqK6twbhuvcVTtjM');
