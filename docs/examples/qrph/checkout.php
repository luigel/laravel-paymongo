<?php

use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050, // PHP 1,500.50, in centavos
    'currency' => 'PHP',
    'payment_method_allowed' => ['qrph'],
    'description' => 'Order ORDER-1234',
]);

$method = Paymongo::paymentMethods()->create(['type' => 'qrph']);

$intent = Paymongo::paymentIntents()->attach($intent->id, $method->id);

$image = $intent->attribute('next_action.code.image_url'); // show it in an <img src="...">
