<?php

use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->retrieve('pi_hsJNpsRFU1LxgVbxW4YJHRs6');

$intent->status;            // ?PaymentIntentStatus
$intent->money()?->format(); // "₱1,500.50"
$intent->lastPaymentError;  // ?array: why the last attempt failed

foreach ($intent->payments as $payment) {
    $payment->id; // "pay_..."
}
