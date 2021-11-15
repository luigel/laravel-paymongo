---
sidebar_position: 1
slug: /
id: getting-started
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

A PHP Library for [Paymongo](https://paymongo.com).

This package is not affiliated with [Paymongo](https://paymongo.com). The package requires PHP 7.2+

## Installation

You can install the package via composer:

```bash
composer require luigel/laravel-paymongo
```

**Laravel 6 and up** uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

Put your `Secret Key` and `Public Key` and the `Webhook secret` in your `.env` file.

```env
# Paymongo
PAYMONGO_SECRET_KEY=
PAYMONGO_PUBLIC_KEY=
# This is the secret from the webhook you created.
PAYMONGO_WEBHOOK_SIG=

```
## Compatibility and Supported Versions

Laravel-Paymongo supports Laravel 6.x and up.

Laravel  | Package
:---------|:----------
5.8.x              | 1.x
6.x.x              | 1.x
7.x.x              | 1.x
8.x.x (PHP 7.4)    | 1.x
8.x.x (PHP 8.0)    | 2.x

## Configuring the package
You can publish the config file by running: 
```bash
php artisan vendor:publish --provider="Luigel\Paymongo\PaymongoServiceProvider" --tag=config
```

This is the contents of the file that will be published at `config/paymongo.php`:
```php
<?php

return [

    'livemode' => env('PAYMONGO_LIVEMODE', false),

    /**
     * Public and Secret keys from Paymongo. You can get the keys here https://dashboard.paymongo.com/developers.
     */

    /**
     * Public keys are meant to be used for any requests coming from the frontend, such as generating tokens or sources,
     * either using Javascript or through the mobile SDKs.
     * Public keys cannot be used to trigger payments or modify any part of the transaction flow.
     * They have the prefix pk_live_ for live mode and pk_test_ for test mode.
     */
    'public_key' => env('PAYMONGO_PUBLIC_KEY', null),

    /**
     * Secret keys, on the other hand, are for triggering or modifying payments. Never share your secret keys anywhere
     * that is publicly accessible: Github, client-side Javascript code, your website or even chat rooms.
     * The prefixes for the secret keys are sk_live_ for live mode and sk_test_ for test mode.
     */
    'secret_key' => env('PAYMONGO_SECRET_KEY', null),

    /**
     * Paymongo's team continuously adding more features and integrations to the API.
     * Currently, the API supports doing payments via debit and credit cards issued by Visa and Mastercard.
     */
    'version' => env('PAYMONGO_VERSION', '2019-08-05'),

    /*
     * This class is responsible for calculating the signature that will be added to
     * the headers of the webhook request. A webhook client can use the signature
     * to verify the request hasn't been tampered with.
     */
    'signer' => \Luigel\Paymongo\Signer\DefaultSigner::class,

    /**
     * Paymongo webhooks signature secret.
     */
    'webhook_signatures' => [
        'payment_paid' => env('PAYMONGO_WEBHOOK_SIG_PAYMENT_PAID', env('PAYMONGO_WEBHOOK_SIG')),
        'payment_failed' => env('PAYMONGO_WEBHOOK_SIG_PAYMENT_FAILED', env('PAYMONGO_WEBHOOK_SIG')),
        'source_chargeable' => env('PAYMONGO_WEBHOOK_SIG_SOURCE_CHARGABLE', env('PAYMONGO_WEBHOOK_SIG')),
    ],

    /**
     * Webhook signature configuration for backwards compatibility.
     */
    'webhook_signature' => env('PAYMONGO_WEBHOOK_SIG'),

    /*
     * This is the name of the header where the signature will be added.
     */
    'signature_header_name' => env('PAYMONGO_SIG_HEADER', 'paymongo-signature'),

    /**
     * This is the amount type to automatically convert the amount in your payload.
     * The default is Paymongo::AMOUNT_TYPE_FLOAT.
     *
     * Choices are: Paymongo::AMOUNT_TYPE_FLOAT, or Paymongo::AMOUNT_TYPE_INT
     */
    'amount_type' => \Luigel\Paymongo\Paymongo::AMOUNT_TYPE_FLOAT,
];

```
