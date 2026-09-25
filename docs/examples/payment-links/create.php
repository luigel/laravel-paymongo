<?php

use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::paymentLinks()->create([
    'amount' => 150050, // PHP 1,500.50, in centavos
    'currency' => 'PHP',
    'description' => 'Invoice INV-1234',
    'remarks' => 'Custom order via Messenger', // for you; customers do not see it
    'metadata' => ['invoice_id' => '1234'],
    'restriction' => ['completed_sessions' => ['limit' => 1]], // stop after one payment
], idempotencyKey: 'invoice-1234-link');

$link->url;             // "https://pm.link/...", send this to your customer
$link->referenceNumber; // the short reference at the end of the URL
