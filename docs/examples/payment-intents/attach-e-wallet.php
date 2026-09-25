<?php

use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Facades\Paymongo;

$method = Paymongo::paymentMethods()->create(['type' => 'gcash']);

$intent = Paymongo::paymentIntents()->attach(
    'pi_hsJNpsRFU1LxgVbxW4YJHRs6',
    $method->id,
    returnUrl: 'https://example.com/orders/1234',
);

if ($intent->status === PaymentIntentStatus::AwaitingNextAction) {
    return redirect()->away($intent->nextAction->url); // GCash's authorization page
}
