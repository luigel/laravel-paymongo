# Paymongo for Laravel

![Run tests](https://github.com/luigel/laravel-paymongo/workflows/Run%20tests/badge.svg)
[![Quality Score](https://img.shields.io/scrutinizer/g/luigel/laravel-paymongo.svg?style=flat-square)](https://scrutinizer-ci.com/g/luigel/laravel-paymongo)
[![Latest Stable Version](https://poser.pugx.org/luigel/laravel-paymongo/v)](//packagist.org/packages/luigel/laravel-paymongo)
[![Total Downloads](https://poser.pugx.org/luigel/laravel-paymongo/downloads)](//packagist.org/packages/luigel/laravel-paymongo)
[![Monthly Downloads](https://poser.pugx.org/luigel/laravel-paymongo/d/monthly)](//packagist.org/packages/luigel/laravel-paymongo)
[![Daily Downloads](https://poser.pugx.org/luigel/laravel-paymongo/d/daily)](//packagist.org/packages/luigel/laravel-paymongo)
[![License](https://poser.pugx.org/luigel/laravel-paymongo/license)](//packagist.org/packages/luigel/laravel-paymongo)

A Laravel client for the [PayMongo](https://paymongo.com) API. Typed responses, first-class webhooks, built-in testing fakes.

This package is not affiliated with PayMongo.

- Full documentation: https://paymongo.rigelkentcarbonel.com/docs
- Upgrading from 2.x? Read the [upgrade guide](UPGRADE.md).

## Requirements

- PHP 8.2 or newer (Laravel 13 itself requires PHP 8.3+)
- Laravel 11, 12, or 13

## Installation

```bash
composer require luigel/laravel-paymongo:^3.0@beta
```

Add your keys to `.env` (grab them from the [PayMongo dashboard](https://dashboard.paymongo.com/developers)):

```env
PAYMONGO_SECRET_KEY=sk_test_...
PAYMONGO_PUBLIC_KEY=pk_test_...
# The endpoint secret_key returned when you register a webhook.
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

Optionally publish the config file to `config/paymongo.php`:

```bash
php artisan vendor:publish --tag=paymongo-config
```

## Example

```php
use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Facades\Paymongo;

// 1. Create the intent. Amounts are integer centavos: 150050 = PHP 1,500.50.
$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card'],
    'description' => 'Order #1234',
]);

// 2. Create a payment method (normally done client-side with your public key).
$method = Paymongo::paymentMethods()->create([
    'type' => 'card',
    'details' => [
        'card_number' => '4343434343434345',
        'exp_month' => 12,
        'exp_year' => 34,
        'cvc' => '123',
    ],
]);

// 3. Attach it to the intent to trigger the payment.
$intent = Paymongo::paymentIntents()->attach($intent->id, $method->id);

if ($intent->status === PaymentIntentStatus::Succeeded) {
    // Paid. $intent->payments holds the resulting Payment resources.
}
```

## Documentation

Guides for every flow (Checkout Sessions, Payment Intents, Payment Links, QR Ph, refunds, subscriptions), webhooks, testing with `Paymongo::fake()`, and the full reference live at https://paymongo.rigelkentcarbonel.com/docs.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
