---
sidebar_position: 2
slug: /payment-intents
id: payment-intents
---

# Payment Intents

## Create Payment Intent

A payment intent is designed to handle a complex payment process. To compare payment intents with tokens, tokens have a straight forward credit card payment process where it does not check if 3DS is required to fulfill a payment while payment intent is designed to handle such process.

### Payload

Refer to [Paymongo documentation](https://developers.paymongo.com/reference/the-payment-intent-object) for payload guidelines.

### Sample

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

## Cancel Payment Intent

Cancels the payment intent.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentIntent = Paymongo::paymentIntent()->find('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
$cancelledPaymentIntent = $paymentIntent->cancel();
```

## Attach Payment Intent

Attach the payment intent.

### Sample
1. Simple attaching of payment method in payment intent.
```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentIntent = Paymongo::paymentIntent()->find('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
// Attached the payment method to the payment intent
$successfulPayment = $paymentIntent->attach('pm_wr98R2gwWroVxfkcNVZBuXg2');
```

2. Attaching paymaya payment method in payment intent.
```php 
$paymentIntent = Paymongo::paymentIntent()
    ->create([
        'amount' => 100,
        'payment_method_allowed' => [
            'paymaya', 'card'  // <--- Make sure to add paymaya here.
        ],
        'payment_method_options' => [
            'card' => [
                'request_three_d_secure' => 'automatic',
            ],
        ],
        'description' => 'This is a test payment intent',
        'statement_descriptor' => 'LUIGEL STORE',
        'currency' => 'PHP',
    ]);

$paymentMethod = Paymongo::paymentMethod()
    ->create([
        'type' => 'paymaya',  // <--- and payment method type should be paymaya
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
            'phone' => '0935454875545',
        ],
    ]);

$attachedPaymentIntent = $paymentIntent->attach($paymentMethod->id, 'http://example.com/success'); // <--- And the second parameter should be the return_url.
```

## Get Payment Intent

You can retrieve a Payment Intent by providing a payment intent ID. The prefix for the id is `pi_` followed by a unique hash representing the payment. Just pass the payment id to `find($paymentIntentId)` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$paymentIntent = Paymongo::paymentIntent()->find('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
```
