<?php

use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card'],
    'setup_future_usage' => [
        'session_type' => 'on_session', // the customer is there to pay again
        'customer_id' => 'cus_b9ENKVqcHBfQQmv26uDYDCsD',
    ],
]);

// Take the payment as usual. Once it succeeds, the card is saved to the customer.
