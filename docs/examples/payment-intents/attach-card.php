<?php

use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->attach(
    'pi_hsJNpsRFU1LxgVbxW4YJHRs6',
    'pm_wr98R2gwWroVxfkcNVZBuXg2', // created by your frontend with the public key
    returnUrl: 'https://example.com/orders/1234',
);

if ($intent->status === PaymentIntentStatus::AwaitingNextAction) {
    return redirect()->away($intent->nextAction->url); // the card's 3D Secure check
}
