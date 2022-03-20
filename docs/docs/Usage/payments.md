---
sidebar_position: 4
slug: /payments
id: payments
---

# Payments

## Create Payment

Creates a payment using source.

### Payload

Refer to [Paymongo documentation](https://developers.paymongo.com/reference/payment-source) for payload guidelines.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$payment = Paymongo::payment()
    ->create([
        'amount' => 100.00,
        'currency' => 'PHP',
        'description' => 'Testing payment',
        'statement_descriptor' => 'Test Paymongo',
        'source' => [
            'id' => $source->id,
            'type' => 'source'
        ]
    ]);
```

## Get Payment

You can retrieve a Payment by providing a payment ID. The prefix for the id is `pay_` followed by a unique hash representing the payment. Just pass the payment id to `find($paymentId)` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$payment = Paymongo::payment()->find('pay_i35wBzLNdX8i9nKEPaSKWGib');
```

## Get All Payments

Returns all the payments you previously created, with the most recent payments returned first. Currently, all payments are returned as one batch. We will be introducing pagination and limits in the next iteration of the API.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$payments = Paymongo::payment()->all();
```
