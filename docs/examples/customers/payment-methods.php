<?php

use Luigel\Paymongo\Facades\Paymongo;

$saved = Paymongo::customers()->paymentMethods('cus_b9ENKVqcHBfQQmv26uDYDCsD');

foreach ($saved as $method) {
    $method->paymentMethodId;         // "pm_...", attach this to a payment intent
    $method->paymentMethodType;       // "card", ...
    $method->sessionType;             // "on_session", ...
    $method->details['last4'] ?? null; // for your "Pay with card ending 4242" button
}

// Forget one, e.g. when the customer removes it from their account:
Paymongo::customers()->deletePaymentMethod('cus_b9ENKVqcHBfQQmv26uDYDCsD', 'pm_wr98R2gwWroVxfkcNVZBuXg2');
