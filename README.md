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
    -   [Payment Intents](#payment-intents)
        -   [Create Payment Intent](#create-payment-intent)
        -   [Cancel Payment Intent](#cancel-payment-intent)
        -   [Attach Payment Intent](#attach-payment-intent)
        -   [Get Payment Intent](#get-payment-intent)
    -   [Sources](#sources)
        -   [Create Source](#create-source)
        -   [Create Payment](#create-payment-source)
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

$paymentMethod = Paymongo::paymentMethod()->find('pm_wr98R2gwWroVxfkcNVZBuXg2');

// You can get data using getData() method
$data = $paymentMethod->getData();

// You can also retrieve specific data using a get method 
$billing = $paymentMethod->getBillingDetails();
```

> ### Payment Intents
>
> ### Create Payment Intent

A payment intent is designed to handle a complex payment process. To compare payment intents with tokens, tokens have a straight forward credit card payment process where it does not check if 3DS is required to fulfill a payment while payment intent is designed to handle such process.

**Payload**

Refer to [Paymongo documentation](https://developers.paymongo.com/reference#post_payment-intents) for payload guidelines.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentIntent = Paymongo::paymentIntent()->create([
    'amount' => 100,
    'payment_method_allowed' => [
        'card'
    ],
    'payment_method_options' => [
        'card' => [
            'request_three_d_secure' => 'automatic'
        ]
    ],
    'description' => 'This is a test payment intent',
    'statement_descriptor' => 'LUIGEL STORE',
    'currency' => "PHP",
]);
```

> ### Cancel Payment Intent

Cancels the payment intent.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentIntent = Paymongo::paymentIntent()->find('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
$cancelledPaymentIntent = $paymentIntent->cancel();
```

 ### Attach Payment Intent

Attach the payment intent. 

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentIntent = Paymongo::paymentIntent()->find('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
// Attached the payment method to the payment intent
$successfulPayment = $paymentIntent->attach('pm_wr98R2gwWroVxfkcNVZBuXg2');
```

> ### Get Payment Intent

You can retrieve a Payment Intent by providing a payment intent ID. The prefix for the id is `pi_` followed by a unique hash representing the payment. Just pass the payment id to `find($paymentIntentId)` method.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentIntent = Paymongo::paymentIntent()->find('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
```


> ### Sources
>
> ### Create Source
>
> Creates a source to let the user pay using their [Gcash Accounts](https://www.gcash.com) or [Grab Pay Accounts](https://www.grab.com/ph/pay/).

**Payload**

Refer to [Paymongo documentation](https://developers.paymongo.com/reference#post_sources-1) for payload guidelines.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

$gcashSource = Paymongo::source()->create([
    'type' => 'gcash',
    'amount' => 100.00,
    'currency' => 'PHP',
    'redirect' => [
        'success' => 'https://your-domain.com/success',
        'failed' => 'https://your-domain.com/failed'
    ]
]);

$grabCarSource = Paymongo::source()->create([
    'type' => 'grab_pay',
    'amount' => 100.00,
    'currency' => 'PHP',
    'redirect' => [
        'success' => 'https://your-domain.com/success',
        'failed' => 'https://your-domain.com/failed'
    ]
]);
```


> ### Sources
>
> ### Create Payment Source
>
> Creates a payment from a Payment Resource.

**Payload**

Refer to [Paymongo documentation](https://developers.paymongo.com/reference#create-a-payment) for payload guidelines.

**Sample**

```php
use Luigel\Paymongo\Facades\Paymongo;

Paymongo::payment()->create([
    'amount' => 100.0,
    'currency' => 'PHP',
    'description' => 'This is a test payment resource',
    'statement_descriptor' => 'LUIGEL STORE',
    'source' => [
        'id' => 'src_cJPbhyqPZFmW5H6AVuVPLTqY',
        'type' => 'source'
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
