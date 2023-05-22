---
sidebar_position: 10
slug: /checkout-sessions
id: checkout-sessions
---

# Checkouts

## Create Checkout

Creates a checkout session. A checkout session is a customizable checkout page from Paymongo.

### Payload

Refer to [Paymongo documentation](https://developers.paymongo.com/reference/checkout-session-resource) for payload guidelines.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$checkout = Paymongo::checkout()->create([
    'cancel_url' => 'https://paymongo.rigelkentcarbonel.com/',
    'billing' => [
        'name' => 'Juan Doe',
        'email' => 'juan@doe.com',
        'phone' => '+639123456789',
    ],
    'description' => 'My checkout session description',
    'line_items' => [
        [
            'amount' => 10000,
            'currency' => 'PHP',
            'description' => 'Something of a product.',
            'images' => [
                'https://images.unsplash.com/photo-1613243555988-441166d4d6fd?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80'
            ],
            'name' => 'A payment card',
            'quantity' => 1
        ]
    ],
    'payment_method_types' => [
        'atome',
        'billease',
        'card',
        'dob',
        'dob_ubp',
        'gcash',
        'grab_pay', 
        'paymaya'
    ],
    'success_url' => 'https://paymongo.rigelkentcarbonel.com/',
    'statement_descriptor' => 'Laravel Paymongo Library',
    'metadata' => [
        'Key' => 'Value'
    ]
]);
```

## Get Checkout

Retrieve a checkout session by passing the id to the `find($id)` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$checkout = Paymongo::checkout()->find('cs_CbFCTDfxvMFNjwjVi26Uzhtj');
```

## Expire Checkout

Expire a checkout session by using the `find($id)` method and chaining the `->expire()` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$checkout = Paymongo::checkout()->find('cs_CbFCTDfxvMFNjwjVi26Uzhtj')->expire();
```