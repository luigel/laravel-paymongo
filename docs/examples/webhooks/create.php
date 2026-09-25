<?php

use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhooks()->create('https://example.com/paymongo/webhook', [
    WebhookEventType::PaymentPaid,
    'payment.failed', // a string works too
]);

$webhook->id;        // "hook_..."
$webhook->secretKey; // "whsk_...": put it in PAYMONGO_WEBHOOK_SECRET
