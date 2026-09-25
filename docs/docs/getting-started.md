---
title: Getting started
slug: getting-started
order: 10
section: Start
---

# Getting Started

## Paymongo for Laravel

![Run tests](https://github.com/luigel/laravel-paymongo/workflows/Run%20tests/badge.svg)
[![Quality Score](https://img.shields.io/scrutinizer/g/luigel/laravel-paymongo.svg?style=flat-square)](https://scrutinizer-ci.com/g/luigel/laravel-paymongo)
[![Latest Stable Version](https://poser.pugx.org/luigel/laravel-paymongo/v)](//packagist.org/packages/luigel/laravel-paymongo)
[![Total Downloads](https://poser.pugx.org/luigel/laravel-paymongo/downloads)](//packagist.org/packages/luigel/laravel-paymongo)
[![Monthly Downloads](https://poser.pugx.org/luigel/laravel-paymongo/d/monthly)](//packagist.org/packages/luigel/laravel-paymongo)
[![Daily Downloads](https://poser.pugx.org/luigel/laravel-paymongo/d/daily)](//packagist.org/packages/luigel/laravel-paymongo)
[![License](https://poser.pugx.org/luigel/laravel-paymongo/license)](//packagist.org/packages/luigel/laravel-paymongo)

A Laravel client for the [PayMongo](https://paymongo.com) API — typed responses, first-class webhooks, and built-in testing fakes.

This package is not affiliated with PayMongo.

:::info Upgrading from 2.x?
v3 is a rewrite. Follow the [upgrade guide](https://github.com/luigel/laravel-paymongo/blob/3.x/UPGRADE.md) — it maps every v2 call to its v3 equivalent.
:::

## Requirements

- PHP 8.2 or newer (Laravel 13 itself requires PHP 8.3+)
- Laravel 11, 12, or 13

## Installation

Install the package via composer:

```bash
composer require luigel/laravel-paymongo
```

The service provider and the `Paymongo` facade alias are auto-discovered.

Put your keys in `.env`. You can get them from the [PayMongo dashboard](https://dashboard.paymongo.com/developers):

```env
PAYMONGO_SECRET_KEY=sk_test_...
PAYMONGO_PUBLIC_KEY=pk_test_...
# The secret_key of the webhook endpoint you registered (see the Webhooks page).
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

## Configuring the package

Publish the config file if you want to change defaults:

```bash
php artisan vendor:publish --tag=paymongo-config
```

This writes `config/paymongo.php`:

```php
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
```

## A first payment

```php
use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Facades\Paymongo;

// Amounts are integer centavos: 150050 = PHP 1,500.50
$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card', 'gcash'],
    'description' => 'Order #1234',
]);

$method = Paymongo::paymentMethods()->create([
    'type' => 'card',
    'details' => [
        'card_number' => '4343434343434345',
        'exp_month' => 12,
        'exp_year' => 34,
        'cvc' => '123',
    ],
]);

$intent = Paymongo::paymentIntents()->attach($intent->id, $method->id);

if ($intent->status === PaymentIntentStatus::Succeeded) {
    $intent->money()->format(); // "₱1,500.50"
}
```

See [Payment Intents](./payment-intents.md) for the full lifecycle including e-wallet redirects.

## Amounts are centavos

Every amount this package sends or returns is an **integer number of centavos** (`150050`, never `1500.50`), exactly as the PayMongo API expects. For display, amount-bearing resources expose `money()`, returning a `Luigel\Paymongo\Support\Money` value object:

```php
$intent->money()->format();    // "₱1,500.50"
$intent->money()->toDecimal(); // "1500.50"
$intent->money()->centavos();  // 150050
```

## Reading responses

Services return immutable DTOs from `Luigel\Paymongo\Data` with typed readonly properties. Enum-valued fields use native enums from `Luigel\Paymongo\Enums` (unknown future API values map to `null`; the raw string stays available). The raw payload is always reachable:

```php
$intent->status;            // ?PaymentIntentStatus (enum)
$intent->attributes;        // full raw attributes array
$intent->attribute('payment_method_options.card.request_three_d_secure');
$intent->toArray();         // ['id' => ..., 'type' => ..., 'attributes' => [...]]
$intent->createdAt();       // ?CarbonImmutable
```

## Handling errors

Failed API calls throw subclasses of `Luigel\Paymongo\Exceptions\PaymongoException`:

```php
use Luigel\Paymongo\Exceptions\InvalidRequestException;

try {
    Paymongo::paymentIntents()->create(['amount' => 50]);
} catch (InvalidRequestException $e) {
    $e->status;               // 400
    $e->firstError()?->code;  // PayMongo error code
    $e->firstError()?->detail;
}
```

The full tree: `AuthenticationException` (401), `PaymentDeclinedException` (402), `ResourceNotFoundException` (404), `RateLimitException` (429, with `->retryAfter`), `ServerException` (5xx), `InvalidRequestException` (other 4xx), `ConnectionException` (network), and `InvalidWebhookSignatureException` (inbound webhooks).

## Multiple accounts

```php
$merchant = Paymongo::withSecretKey($tenant->paymongo_secret_key);

$merchant->paymentIntents()->create([...]);
```

## Compatibility and supported versions

| Package | Laravel | PHP | Status |
|:--------|:--------|:----|:-------|
| 3.x | 11.x – 13.x | 8.2+ (8.3+ for Laravel 13) | Active |
| 2.x | 8.x – 13.x | 8.0+ | Maintenance only |
| 1.x | 5.8 – 8.x | 7.2+ | End of life |
