<?php

return [
    'secret_key' => env('PAYMONGO_SECRET_KEY'),
    'public_key' => env('PAYMONGO_PUBLIC_KEY'),
    'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
    'livemode' => env('PAYMONGO_LIVEMODE', false),

    'http' => [
        'timeout' => (int) env('PAYMONGO_TIMEOUT', 30),
        // Retries after the first attempt; 0 disables retrying.
        'retries' => (int) env('PAYMONGO_RETRIES', 2),
        // Base delay in milliseconds; it doubles on every retry, with jitter.
        'retry_delay' => (int) env('PAYMONGO_RETRY_DELAY', 200),
        // Longest single wait in milliseconds. A Retry-After beyond it is not waited for.
        'max_retry_delay' => (int) env('PAYMONGO_MAX_RETRY_DELAY', 5000),
    ],

    'idempotency' => [
        'auto' => (bool) env('PAYMONGO_AUTO_IDEMPOTENCY', true),
    ],

    'webhooks' => [
        // Default signing secret (webhook endpoint's secret_key from PayMongo).
        'secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        // Named secrets for multiple endpoints: ['orders' => env(...)]
        'secrets' => [],
        // Named endpoint modes: ['orders' => false]. Unlisted names use livemode above.
        'modes' => [],
        // Max allowed clock drift for the signature timestamp, seconds. 0 disables the check.
        'tolerance' => (int) env('PAYMONGO_WEBHOOK_TOLERANCE', 300),
        'dedupe' => [
            'enabled' => (bool) env('PAYMONGO_WEBHOOK_DEDUPE', true),
            'ttl' => 86400,
            'store' => env('PAYMONGO_WEBHOOK_DEDUPE_STORE'),
        ],
    ],
];
