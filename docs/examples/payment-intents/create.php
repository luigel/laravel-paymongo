<?php

use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
    'payment_method_options' => [
        'card' => ['request_three_d_secure' => 'automatic'],
    ],
    'description' => 'Order #1234',
    'statement_descriptor' => 'LUIGEL STORE',
    'metadata' => ['order_id' => '1234'],
]);

$intent->id;        // "pi_hsJNpsRFU1LxgVbxW4YJHRs6"
$intent->clientKey; // pass to your frontend for client-side confirmation
