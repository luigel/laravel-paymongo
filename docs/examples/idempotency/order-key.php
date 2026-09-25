<?php

use Luigel\Paymongo\Facades\Paymongo;

$orderReference = 'ORDER-1234';

// The same key for the same order: a repeat returns the intent already created.
$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card', 'gcash'],
    'metadata' => ['order_reference' => $orderReference],
], idempotencyKey: "{$orderReference}-payment-intent");
