---
title: Payment Methods
slug: payment-methods
order: 22
section: Accept payments
---

# Payment Methods

A payment method holds the instrument used to pay — card details, an e-wallet type, billing information. Attach one to a [payment intent](./payment-intents.md) to charge it.

All methods live on `Paymongo::paymentMethods()` and return `Luigel\Paymongo\Data\PaymentMethod` DTOs. See the [PayMongo documentation](https://developers.paymongo.com/reference/the-payment-method-object) for the payload.

:::tip
In production, create card payment methods client-side with your **public key** (PayMongo JS) so card numbers never touch your server; then attach the resulting `pm_...` id server-side.
:::

## Create

```php
use Luigel\Paymongo\Facades\Paymongo;

$method = Paymongo::paymentMethods()->create([
    'type' => 'card',
    'details' => [
        'card_number' => '4343434343434345',
        'exp_month' => 12,
        'exp_year' => 34,
        'cvc' => '123',
    ],
    'billing' => [
        'name' => 'Juan dela Cruz',
        'email' => 'juan@example.com',
        'phone' => '+639171234567',
        'address' => [
            'line1' => '123 Osmena Blvd',
            'city' => 'Cebu City',
            'state' => 'Cebu',
            'country' => 'PH',
            'postal_code' => '6000',
        ],
    ],
]);
```

E-wallet methods only need a type:

```php
$gcash = Paymongo::paymentMethods()->create(['type' => 'gcash']);
```

The supported types are the cases of `Luigel\Paymongo\Enums\PaymentMethodType` (you may pass the enum or the plain string): `card`, `gcash`, `grab_pay`, `paymaya`, `shopee_pay`, `qrph`, `billease`, `dob`, `brankas`, `atome`.

## Retrieve

```php
$method = Paymongo::paymentMethods()->retrieve('pm_wr98R2gwWroVxfkcNVZBuXg2');

$method->methodType;              // ?PaymentMethodType — the `type` attribute (card, gcash, ...)
$method->billing?->name;          // typed Billing / Address value objects
$method->billing?->address?->city;
$method->details;                 // raw details array (masked card info, ...)
```
