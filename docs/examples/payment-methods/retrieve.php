<?php

use Luigel\Paymongo\Facades\Paymongo;

$method = Paymongo::paymentMethods()->retrieve('pm_wr98R2gwWroVxfkcNVZBuXg2');

$method->methodType;               // ?PaymentMethodType: Card, Gcash, ...
$method->billing?->name;           // "Juan dela Cruz"
$method->billing?->address?->city; // "Cebu City"
$method->details;                  // ?array, e.g. the card's last4 and exp_month; never the full number
