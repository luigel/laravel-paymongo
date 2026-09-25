<?php

use Luigel\Paymongo\Facades\Paymongo;

$customer = Paymongo::customers()->retrieve('cus_b9ENKVqcHBfQQmv26uDYDCsD');

$customer->email;
$customer->defaultDevice;          // ?DefaultDevice: Phone or Email
$customer->defaultPaymentMethodId; // "pm_..." once a card is vaulted

$customer = Paymongo::customers()->update('cus_b9ENKVqcHBfQQmv26uDYDCsD', [
    'email' => 'juan.delacruz@example.com', // only the keys you pass change
]);
