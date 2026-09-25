<?php

use Luigel\Paymongo\Facades\Paymongo;

$merchantSecretKey = 'sk_test_merchant_account'; // e.g. decrypt($merchant->paymongo_secret_key)

$paymongo = Paymongo::withSecretKey($merchantSecretKey);

$intent = $paymongo->paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card', 'gcash'],
]);

// Paymongo::paymentIntents() still uses PAYMONGO_SECRET_KEY.
