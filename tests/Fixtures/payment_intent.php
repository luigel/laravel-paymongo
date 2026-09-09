<?php

declare(strict_types=1);

return [
    'data' => [
        'id' => 'pi_UWL2ZP2rBjMPS9UfnqAROSXg',
        'type' => 'payment_intent',
        'attributes' => [
            'amount' => 150050,
            'capture_type' => 'automatic',
            'client_key' => 'pi_UWL2ZP2rBjMPS9UfnqAROSXg_client_hVvMV6nHFvpaXV2EYVMTLNSZ',
            'currency' => 'PHP',
            'description' => 'Order #10101',
            'livemode' => false,
            'statement_descriptor' => 'LUIGEL STORE',
            'status' => 'awaiting_payment_method',
            'last_payment_error' => null,
            'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
            'payments' => [],
            'next_action' => null,
            'payment_method_options' => [
                'card' => ['request_three_d_secure' => 'any'],
            ],
            'metadata' => ['order_id' => '10101'],
            'setup_future_usage' => null,
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ],
    ],
];
