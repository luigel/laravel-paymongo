<?php

use Luigel\Paymongo\Enums\PaymentStatus;
use Luigel\Paymongo\Facades\Paymongo;

$payment = Paymongo::payments()->retrieve('pay_i7tdqnmwdszWo5B4Xqk2ogX5');

$payment->status === PaymentStatus::Paid;
$payment->money()?->format(); // "₱1,500.50"
$payment->fee;                // PayMongo's fee, in centavos
$payment->netAmount;          // what you keep, in centavos
$payment->paymentIntentId;    // "pi_...", the intent it paid
$payment->billing?->email;
$payment->paidAt();           // ?CarbonImmutable
$payment->attribute('source.type'); // "card", "gcash", ...
