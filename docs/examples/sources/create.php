<?php

use Luigel\Paymongo\Facades\Paymongo;

$source = Paymongo::sources()->create([
    'type' => 'gcash', // or grab_pay
    'amount' => 150050, // centavos, at least 10000 (PHP 100.00)
    'currency' => 'PHP',
    'redirect' => [
        'success' => 'https://example.com/orders/1234/paid',
        'failed' => 'https://example.com/orders/1234/failed',
    ],
]);

$checkoutUrl = $source->redirect?->checkoutUrl; // send the customer here to authorize
