<?php

use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::links()->create([
    'amount' => 150050, // PHP 1,500.50, in centavos
    'description' => 'Invoice INV-1234',
    'remarks' => 'Custom order via Messenger',
]);

$link->checkoutUrl;     // send this to your customer
$link->referenceNumber; // e.g. "WTmSJbV"
