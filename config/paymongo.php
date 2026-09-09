<?php

return [
    'secret_key' => env('PAYMONGO_SECRET_KEY'),
    'public_key' => env('PAYMONGO_PUBLIC_KEY'),
    'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
    'livemode' => env('PAYMONGO_LIVEMODE', false),

    'http' => [
        'timeout' => (int) env('PAYMONGO_TIMEOUT', 30),
        'retries' => (int) env('PAYMONGO_RETRIES', 2),
        'retry_delay' => (int) env('PAYMONGO_RETRY_DELAY', 200),
    ],

    'idempotency' => [
        'auto' => (bool) env('PAYMONGO_AUTO_IDEMPOTENCY', true),
    ],

    'webhooks' => [
        // Default signing secret (webhook endpoint's secret_key from PayMongo).
        'secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        // Named secrets for multiple endpoints: ['orders' => env(...)]
        'secrets' => [],
        // Max allowed clock drift for the signature timestamp, seconds. 0 disables the check.
        'tolerance' => (int) env('PAYMONGO_WEBHOOK_TOLERANCE', 300),
        'dedupe' => [
            'enabled' => (bool) env('PAYMONGO_WEBHOOK_DEDUPE', true),
            'ttl' => 86400,
            'store' => env('PAYMONGO_WEBHOOK_DEDUPE_STORE'),
        ],
    ],
];
