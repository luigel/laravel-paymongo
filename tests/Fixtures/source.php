<?php

declare(strict_types=1);

return [
    'data' => [
        'id' => 'src_hE2Fx8sBoGrVjqZQY6nDdT4c',
        'type' => 'source',
        'attributes' => [
            'amount' => 150050,
            'billing' => [
                'address' => [
                    'city' => 'Taguig',
                    'country' => 'PH',
                    'line1' => '212 Sesame St.',
                    'line2' => null,
                    'postal_code' => '1630',
                    'state' => 'Metro Manila',
                ],
                'email' => 'juan.delacruz@example.com',
                'name' => 'Juan Dela Cruz',
                'phone' => '+639171234567',
            ],
            'currency' => 'PHP',
            'description' => null,
            'livemode' => false,
            'redirect' => [
                'checkout_url' => 'https://secure-authentication.paymongo.com/sources?id=src_hE2Fx8sBoGrVjqZQY6nDdT4c',
                'failed' => 'https://example.com/payments/failed',
                'success' => 'https://example.com/payments/success',
            ],
            'statement_descriptor' => null,
            'status' => 'pending',
            'type' => 'gcash',
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ],
    ],
];
