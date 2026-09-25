<?php

use Luigel\Paymongo\Facades\Paymongo;

$source = Paymongo::sources()->retrieve('src_hsJNpsRFU1LxgVbxW4YJHRs6');

$source->status;             // ?string: "pending", "chargeable", "cancelled", "expired" or "paid"
$source->sourceType;         // ?PaymentMethodType: Gcash or GrabPay
$source->money()?->format(); // "₱1,500.50"
$source->redirect?->success;
