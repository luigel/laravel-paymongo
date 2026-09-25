<?php

use Luigel\Paymongo\Enums\WebhookStatus;
use Luigel\Paymongo\Facades\Paymongo;

foreach (Paymongo::webhooks()->list()->lazy() as $webhook) { // every endpoint, one request per page
    $webhook->url;
    $webhook->events;                              // list<string>
    $webhook->status === WebhookStatus::Enabled;   // ?WebhookStatus
}

$webhook = Paymongo::webhooks()->retrieve('hook_9VrvpRkkYqK6twbhuvcVTtjM');
