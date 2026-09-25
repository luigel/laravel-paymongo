---
title: Customers
slug: customers
order: 40
section: Recurring
---

# Customers

A customer holds contact details and vaulted payment methods, and is required for [subscriptions](./subscriptions.md).

All methods live on `Paymongo::customers()` and return `Luigel\Paymongo\Data\Customer` DTOs. See the [PayMongo documentation](https://developers.paymongo.com/reference/customer-resource) for payload guidelines.

## Create

```php
use Luigel\Paymongo\Facades\Paymongo;

$customer = Paymongo::customers()->create([
    'first_name' => 'Juan',
    'last_name' => 'dela Cruz',
    'phone' => '+639171234567',
    'email' => 'juan@example.com',
    'default_device' => 'phone', // or 'email' (enum Luigel\Paymongo\Enums\DefaultDevice)
]);

$customer->id; // "cus_b9ENKVqcHBfQQmv26uDYDCsD"
```

## Retrieve

```php
$customer = Paymongo::customers()->retrieve('cus_b9ENKVqcHBfQQmv26uDYDCsD');

$customer->firstName;
$customer->email;
$customer->defaultDevice;          // ?DefaultDevice
$customer->defaultPaymentMethodId; // "pm_..." when one is set
```

## Update

```php
$customer = Paymongo::customers()->update('cus_b9ENKVqcHBfQQmv26uDYDCsD', [
    'first_name' => 'Jane',
]);
```

## Delete

```php
Paymongo::customers()->delete('cus_b9ENKVqcHBfQQmv26uDYDCsD'); // returns true; throws on failure
```

## Saved payment methods

List the payment methods vaulted against a customer — returned as `Luigel\Paymongo\Data\CustomerPaymentMethod` (resource type `customer_payment_method`):

```php
$saved = Paymongo::customers()->paymentMethods('cus_b9ENKVqcHBfQQmv26uDYDCsD');

foreach ($saved as $item) {
    $item->paymentMethodId;   // the underlying "pm_..." id
    $item->paymentMethodType; // "card", "gcash", ...
}
```

Remove one:

```php
Paymongo::customers()->deletePaymentMethod('cus_b9ENKVqcHBfQQmv26uDYDCsD', 'pm_wr98R2gwWroVxfkcNVZBuXg2');
```
