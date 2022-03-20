# Changelog

All notable changes to `laravel-paymongo` will be documented in this file

## v2.2.0 - 2022-03-20

## What's Changed

- Apply fixes from StyleCI by @luigel in https://github.com/luigel/laravel-paymongo/pull/66
- Bump url-parse from 1.5.3 to 1.5.10 in /docs by @dependabot in https://github.com/luigel/laravel-paymongo/pull/64
- Bump prismjs from 1.25.0 to 1.27.0 in /docs by @dependabot in https://github.com/luigel/laravel-paymongo/pull/63
- Bump follow-redirects from 1.14.7 to 1.14.8 in /docs by @dependabot in https://github.com/luigel/laravel-paymongo/pull/61

## Added

- Support for Refund API. https://developers.paymongo.com/docs/refunding-transactions
- Fixes issue https://github.com/luigel/laravel-paymongo/issues/65

**Full Changelog**: https://github.com/luigel/laravel-paymongo/compare/v2.1.2...v2.2.0

## 1.3.0 (2020-10-31)

### Added

- Added `getData()` for all the models.
- It can now access the properties of the data directly. (eg. `$token->id`)
- Added magic methods to all the models to get the specific data. Using the `get` prefix for the method.

#### Example:

```php
// Get the ID of the token.
$id = $token->getId();

// Get the billing details of the payment method.
$paymentMethod->getBilling();

// Get the billing name of the payment method.
$paymentMethod->getBillingName();

```
- Added artisan commands for webhooks.
