<?php

use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050, // PHP 1,500.50, in centavos
    'currency' => 'PHP',
    'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
    'payment_method_options' => [
        'card' => ['request_three_d_secure' => 'automatic'],
    ],
    'description' => 'Order ORDER-1234',
    'statement_descriptor' => 'LUIGEL STORE',
    'metadata' => ['order_id' => '1234'],
], idempotencyKey: 'order-1234-payment');

$intent->id;        // "pi_hsJNpsRFU1LxgVbxW4YJHRs6"
$intent->clientKey; // for your frontend, if it attaches the payment method
