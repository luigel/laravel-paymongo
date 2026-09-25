<?php

use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhooks()->update('hook_9VrvpRkkYqK6twbhuvcVTtjM', [
    'url' => 'https://example.com/paymongo/webhook',
    'events' => ['payment.paid', 'payment.failed', 'payment.refunded'],
]);
