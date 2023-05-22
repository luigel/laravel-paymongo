# Changelog

All notable changes to `laravel-paymongo` will be documented in this file

## 2.4.0 (2023-04-30)

### Added
- Checkouts API

## 2.3.0 (2022-12-15)

### Added
- Links API
- Customers API

### Fixed
- Failing tests

## 1.3.0 (2020-10-31)

### Added

-   Added `getData()` for all the models.
-   It can now access the properties of the data directly. (eg. `$token->id`)
-   Added magic methods to all the models to get the specific data. Using the `get` prefix for the method.

#### Example:

```php
// Get the ID of the token.
$id = $token->getId();

// Get the billing details of the payment method.
$paymentMethod->getBilling();

// Get the billing name of the payment method.
$paymentMethod->getBillingName();
```

-   Added artisan commands for webhooks.
