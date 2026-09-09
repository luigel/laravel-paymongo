---
sidebar_position: 4
slug: /refunds
id: refunds
---

# Refunds

Refund a paid payment, fully or partially, back to the original payment method.

All methods live on `Paymongo::refunds()` and return `Luigel\Paymongo\Data\Refund` DTOs. See the [PayMongo documentation](https://developers.paymongo.com/reference/refund-resource) for payload guidelines.

## Create

The amount is integer centavos; the reason is required and must be one of the `Luigel\Paymongo\Enums\RefundReason` cases — `Duplicate` (`duplicate`), `Fraudulent` (`fraudulent`), or `Others` (`others`). Enum or plain string both work:

```php
use Luigel\Paymongo\Enums\RefundReason;
use Luigel\Paymongo\Facades\Paymongo;

$refund = Paymongo::refunds()->create([
    'amount' => 50050, // partial refund of PHP 500.50
    'payment_id' => 'pay_i35wBzLNdX8i9nKEPaSKWGib',
    'reason' => RefundReason::Duplicate,
    'notes' => 'Customer was charged twice',
]);
```

Like all creates, refunds send an automatic `Idempotency-Key`; pass your own as the second argument when you want retry-safety tied to your own identifier:

```php
$refund = Paymongo::refunds()->create($attributes, idempotencyKey: "refund-{$order->uuid}");
```

## Retrieve

```php
$refund = Paymongo::refunds()->retrieve('ref_rBCmgwgMXZ9VH4YS2eRooPVL');

$refund->status;   // ?RefundStatus (Pending | Succeeded | Failed)
$refund->reason;   // ?RefundReason
$refund->paymentId;
$refund->money()->format(); // "₱500.50"
$refund->refundedAt();      // ?CarbonImmutable
```

## List

Returns a `CursorPage<Refund>`; filter by payment with `payment_id`:

```php
$page = Paymongo::refunds()->list(['payment_id' => 'pay_i35wBzLNdX8i9nKEPaSKWGib']);

foreach ($page as $refund) {
    // ...
}

// All refunds, lazily, across pages:
Paymongo::refunds()->list()->lazy()->each(function ($refund) {
    // ...
});
```

Supported list parameters: `limit`, `before`, `after`, `payment_id`.
