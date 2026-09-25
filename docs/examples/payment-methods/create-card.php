<?php

use Luigel\Paymongo\Facades\Paymongo;

$method = Paymongo::paymentMethods()->create([
    'type' => 'card',
    'details' => [
        'card_number' => '4343434343434345', // a test card
        'exp_month' => 12,
        'exp_year' => 34,
        'cvc' => '123',
    ],
    'billing' => [
        'name' => 'Juan dela Cruz',
        'email' => 'juan@example.com',
        'phone' => '+639171234567',
        'address' => [
            'line1' => '123 Osmeña Blvd',
            'city' => 'Cebu City',
            'state' => 'Cebu',
            'postal_code' => '6000',
            'country' => 'PH',
        ],
    ],
]);

$method->id; // "pm_...", to attach to a payment intent
