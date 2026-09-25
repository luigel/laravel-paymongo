<?php

use Luigel\Paymongo\Facades\Paymongo;

$price = '1500.50'; // pesos, e.g. from a form or a DECIMAL(10, 2) column

$centavos = (int) round((float) $price * 100); // 150050

$intent = Paymongo::paymentIntents()->create([
    'amount' => $centavos,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card'],
]);

$intent->amount;             // 150050: amounts come back in centavos too
$intent->money()?->format(); // "₱1,500.50"
