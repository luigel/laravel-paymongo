---
sidebar_position: 3
slug: /payments
id: payments
---

# Payments

A payment is the money movement itself. You do not create payments directly — PayMongo creates one when a [payment intent](./payment-intents.md) succeeds (or when a checkout session or link is paid). This service is read-only.

All methods live on `Paymongo::payments()` and return `Luigel\Paymongo\Data\Payment` DTOs.

## Retrieve

```php
use Luigel\Paymongo\Facades\Paymongo;

$payment = Paymongo::payments()->retrieve('pay_i35wBzLNdX8i9nKEPaSKWGib');

$payment->amount;            // 150050 (centavos)
$payment->money()->format(); // "₱1,500.50"
$payment->status;            // ?PaymentStatus (Pending | Paid | Failed | Refunded | PartiallyRefunded)
$payment->fee;               // PayMongo fee in centavos
$payment->netAmount;         // what you receive, in centavos
$payment->paymentIntentId;   // "pi_..."
$payment->billing?->email;
$payment->paidAt();          // ?CarbonImmutable
```

## List

Listing is cursor-paginated and returns a `CursorPage<Payment>`:

```php
$page = Paymongo::payments()->list(['limit' => 25]);

foreach ($page as $payment) {
    // ...
}

$page->hasMore;             // bool
$next = $page->nextPage();  // ?CursorPage — fetched with the `after` cursor
```

Walk every payment across all pages lazily (one request per page):

```php
Paymongo::payments()->list()->lazy()->each(function ($payment) {
    // ...
});
```

Supported list parameters: `limit`, `before`, `after`.
