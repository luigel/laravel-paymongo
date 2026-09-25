<?php

use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['gcash'],
]);

$method = Paymongo::paymentMethods()->create(['type' => 'gcash']);

$intent = Paymongo::paymentIntents()->attach($intent->id, $method->id, returnUrl: 'https://example.com/orders/1234');

$authorizeUrl = $intent->nextAction?->url; // redirect the customer here, then listen for payment.paid
