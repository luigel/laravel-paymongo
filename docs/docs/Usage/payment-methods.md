---
sidebar_position: 1
slug: /payment-methods
id: payment-methods
---

# Payment Methods

## Create Payment Method

Creates a payment methods. It holds the information such as credit card information and billing information.

### Payload

Refer to [Paymongo documentation](https://developers.paymongo.com/reference/the-payment-method-object) for payload.

### Sample

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

## Get Payment Method

Retrieve a payment method given an ID. Just pass the payment method id to `find($id)` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentMethod = Paymongo::paymentMethod()->find('pm_wr98R2gwWroVxfkcNVZBuXg2');
```
