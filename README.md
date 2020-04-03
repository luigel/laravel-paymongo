> # Paymongo for Laravel

[![Build Status](https://travis-ci.com/luigel/laravel-paymongo.svg?branch=master)](https://travis-ci.com/luigel/laravel-paymongo)
[![Quality Score](https://img.shields.io/scrutinizer/g/luigel/laravel-paymongo.svg?style=flat-square)](https://scrutinizer-ci.com/g/luigel/laravel-paymongo)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/luigel/laravel-paymongo.svg?style=flat-square)](https://packagist.org/packages/luigel/laravel-paymongo)
[![Total Downloads](https://img.shields.io/packagist/dt/luigel/laravel-paymongo.svg?style=flat-square)](https://packagist.org/packages/luigel/laravel-paymongo)
[![License](https://img.shields.io/github/license/luigel/laravel-paymongo.svg?style=flat-square)](https://github.com/luigel/laravel-paymongo/blob/master/LICENSE.md)

A PHP Library for [Paymongo](https://paymongo.com).

This package is not affiliated with [Paymongo](https://paymongo.com). The package requires PHP 7.2+

-   [Paymongo for Laravel](#paymongo-for-laravel)
    -   [Installation](#installation)
    -   [Compatibility and Supported Versions](#compatibility-and-supported-versions)
    -   [Usage](#usage)
    -   [Payment Methods](#payment-methods)
        -   [Create Payment Method](#create-payment-method)
        -   [Get Payment Method](#get-payment-method)
    -   [Payments](#payments)
        -   [Create Payment](#create-payment)
        -   [Get Payment](#get-payment)
        -   [Get Payments](#get-all-payments)
    -   [Sources](#sources)
        -   [Create Source](#create-source)
    -   [Webhooks](#webhooks)
        -   [Create Webhook](#create-webhook)
        -   [List all Webhooks](#list-all-webhooks)
        -   [Enable or Disable Webhooks](#enable-or-disable-webhooks)
        -   [Update Webhook](#update-webhook)

> ## Installation

You can install the package via composer:

```bash
composer require luigel/laravel-paymongo
```

**Laravel 6 and up** uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

Put your `Secret Key` and `Public Key` in you `.env` file.

```
PAYMONGO_SECRET_KEY=
PAYMONGO_PUBLIC_KEY=
```

> ## Compatibility and Supported Versions
Laravel-Paymongo supports Laravel 6.* and 7.*

> ## Usage

> ### Payment Methods
>
> ### Create Payment Method
Creates a payment methods. It holds the information such as credit card information and billing information.

**Payload**

Refer to [Paymongo documentation](https://developers.paymongo.com/reference#post_payment-methods) for payload.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentMethod = Paymongo::paymentMethod()->create([
    'type' => 'card',
    'details' => [
        'card_number' => '4343434343434345',
        'exp_month' => 12,
        'exp_year' => 25,
        'cvc' => "123",
    ],
    'billing' => [
        'address' => [
            'line1' => 'Somewhere there',
            'city' => 'Cebu City',
            'state' => 'Cebu',
            'country' => 'PH',
            'postal_code' => '6000',
        ],
        'name' => 'Rigel Kent Carbonel',
        'email' => 'rigel20.kent@gmail.com',
        'phone' => '0935454875545'
    ],
]);
```

> ### Get Payment Method

Retrieve a payment method given an ID. Just pass the payment method id to `find($id)` method.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentMethod = Paymongo::paymentMethod()->find($tokenId);

// You can get data using getData() method
$data = $paymentMethod->getData();

// You can also retrieve specific data using a get method 
$billing = $paymentMethod->getBillingDetails();
```

> ### Payments
>
> ### Create Payment

To charge a payment source, you must create a Payment object. When in test mode, your payment sources won't actually be charged. You can select specific payment sources for different success and failure scenarios.

**Payload**

Refer to [Paymongo documentation](https://developers.paymongo.com/reference#create-a-payment) for payload guidelines.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$payment = Paymongo::payment()->create([
    'amount' => 100.00,
    'currency' => 'PHP',
    'description' => 'A testing Payment',
    'statement_descriptor' => 'LUIGEL STORE',
    'source' => [
        'id' => 'tok_Jt7WJhH4eQaEEZqWxdXW3sz5',
        'type' => 'token'
    ]
]);
```

> ### Get Payment

You can retrieve a Payment by providing a payment ID. The prefix for the id is `pay_` followed by a unique hash representing the payment. Just pass the payment id to `find($paymentId)` method.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$payment = Paymongo::payment()->find('pay_i35wBzLNdX8i9nKEPaSKWGib');
```

> ### Get all Payments

Returns all the payments you previously created, with the most recent payments returned first. Currently, all payments are returned as one batch. We will be introducing pagination and limits in the next iteration of the API.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$payments = Paymongo::payment()->all();
```

> ### Sources
>
> ### Create Source
>
> Creates a source to let the user pay using their [Gcash Accounts](https://www.gcash.com).

**Payload**

Refer to [Paymongo documentation](https://developers.paymongo.com/reference#post_sources-1) for payload guidelines.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$source = Paymongo::source()->create([
    'type' => 'gcash',
    'amount' => 100.00,
    'currency' => 'PHP',
    'redirect' => [
        'success' => 'https://your-domain.com/success',
        'failed' => 'https://your-domain.com/failed'
    ]
]);
```

> ### Webhooks
>
> ### Create Webhook
>
> Creates a webhook.

**Payload**

Refer to [Paymongo documentation](https://developers.paymongo.com/reference#post_webhooks-1) for payload guidelines.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhook()->create([
    'url' => 'http://your-domain/webhook/source-chargeable',
    'events' => [
        'source.chargeable'
    ]
]);
```

> ### List all Webhooks

Returns all the webhooks you previously created, with the most recent webhooks returned first.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhook()->all();
```

> ### Enable or Disable Webhooks

Set the webhook enable or disable.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;
// Enable webhook
$webhook = Paymongo::webhook()->find('hook_9VrvpRkkYqK6twbhuvcVTtjM')->enable();

// Disable webhook
$webhook = Paymongo::webhook()->find('hook_9VrvpRkkYqK6twbhuvcVTtjM')->disable();
```

> ### Update Webhook

Updates a specific webhook

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhook()->find('hook_9VrvpRkkYqK6twbhuvcVTtjM')->update([
    'url' => 'https://update-domain.com/webhook'
]);
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email rigel20.kent@gmail.com instead of using the issue tracker.

## Credits

-   [Rigel Kent Carbonel](https://github.com/luigel)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

Made with :heart: by Rigel Kent Carbonel
