<?php

use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::links()->create([
    'amount' => 150050, // v2 took 1500.50
    'description' => 'Invoice #1234',
]);

$link->amount;               // 150050, where v2's getAmount() returned 1500.5
$link->money()?->format();   // "₱1,500.50"
$link->money()?->toDecimal(); // "1500.50"
