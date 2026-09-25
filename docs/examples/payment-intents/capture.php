<?php

use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050, // PHP 1,500.50, in centavos
    'currency' => 'PHP',
    'payment_method_allowed' => ['card'],
    'capture_type' => 'manual',
]);

// ...attach a card; the intent then waits in awaiting_capture.

$intent = Paymongo::paymentIntents()->capture($intent->id);

// Or capture less than was authorized, in centavos:
$intent = Paymongo::paymentIntents()->capture($intent->id, 100000);
