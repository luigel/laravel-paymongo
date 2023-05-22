---
sidebar_position: 9
slug: /refunds
id: refunds
---

# Refunds

## Create Refund

Performs a refund to a customer's paid payments in full or a partial amount to the original payment method used. 

### Payload

Refer to [Paymongo documentation](https://developers.paymongo.com/reference/refund-resource) for payload guidelines.

### Sample

Here are the possible values of the reasons.
- \Luigel\Paymongo\Models\Refund::REASON_DUPLICATE
- \Luigel\Paymongo\Models\Refund::REASON_FRAUDULENT
- \Luigel\Paymongo\Models\Refund::REASON_REQUESTED_BY_CUSTOMER
- \Luigel\Paymongo\Models\Refund::REASON_OTHERS

```php
use Luigel\Paymongo\Facades\Paymongo;

$refund = Paymongo::refund()->create([
   'amount' => 10,
   'notes' => 'test refund',
   'payment_id' => $payment->id,
   'reason' => \Luigel\Paymongo\Models\Refund::REASON_DUPLICATE,
]);
```

## Get Refund

You can retrieve a Refund by providing a refund ID. The prefix for the id is `ref_` followed by a unique hash representing the payment. Just pass the refund id to `find($refundId)` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$payment = Paymongo::refund()->find('ref_rBCmgwgMXZ9VH4YS2eRooPVL');
```

## Get All Refunds

Returns all the refunds you previously created, with the most recent refunds returned first.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$payments = Paymongo::refund()->all();
```
