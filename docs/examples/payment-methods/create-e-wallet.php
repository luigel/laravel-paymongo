<?php

use Luigel\Paymongo\Enums\PaymentMethodType;
use Luigel\Paymongo\Facades\Paymongo;

$gcash = Paymongo::paymentMethods()->create(['type' => PaymentMethodType::Gcash]);

$qrph = Paymongo::paymentMethods()->create([
    'type' => 'qrph',
    'expiry_seconds' => 900, // the QR code expires 15 minutes after it is attached
]);
