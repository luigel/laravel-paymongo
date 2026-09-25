<?php

use Luigel\Paymongo\Facades\Paymongo;

// Once the source is chargeable (the source.chargeable webhook), charge it:
$payment = Paymongo::payments()->create([
    'amount' => 150050, // centavos, the source's amount
    'currency' => 'PHP',
    'source' => ['id' => 'src_hsJNpsRFU1LxgVbxW4YJHRs6', 'type' => 'source'],
    'description' => 'Order #1234',
], idempotencyKey: 'order-1234-charge');

$payment->status; // ?PaymentStatus: Paid, or Failed with $payment->attribute('failed_message')
