---
title: Payment Links
slug: payment-links
order: 23
section: Accept payments
---

# Payment Links

PayMongo's newer `/payment_links` API — a shareable payment URL like [Links](./links.md), on a different API surface. New in v3.

All methods live on `Paymongo::paymentLinks()` and return `Luigel\Paymongo\Data\PaymentLink` DTOs. The legacy `/links` API stays available unchanged as `Paymongo::links()`.

## How it differs from the legacy Links API

| | `Paymongo::links()` (legacy `/links`) | `Paymongo::paymentLinks()` (`/payment_links`) |
|---|---|---|
| Request body | `data.attributes` envelope | **Flat** JSON body |
| Response | `{id, type, attributes}` resource | **Flat** object (fields directly on `data`) |
| Timestamps | Unix seconds | **ISO 8601 strings** |
| `status` | Payment state (`unpaid` / `paid` / `archived`) | Management state (`active` / `archived`) |
| Archiving | `POST .../archive` and `.../unarchive` | `PATCH` with `status` (`archive()` / `unarchive()` shorthands) |

The package absorbs all of this — you still pass plain attribute arrays and get typed DTOs back.

## Create

```php
use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::paymentLinks()->create([
    'amount' => 150050, // PHP 1,500.50 in centavos, min 100
    'currency' => 'PHP',
    'description' => 'Invoice #1234',
    'remarks' => 'laravel-paymongo',
    'restrictions' => ['completed_sessions' => 1], // e.g. stop accepting after 1 paid session
]);

$link->id;     // "plink_uSJXoxTBNqRrg35kj5w9dTVY"
$link->url;    // share this with your customer
$link->status; // ?PaymentLinkStatus (Active | Archived)
```

Optional: `metadata`. `create()` also accepts an idempotency key: `create($attributes, idempotencyKey: $orderUuid)`.

## Retrieve and update

```php
$link = Paymongo::paymentLinks()->retrieve('plink_uSJXoxTBNqRrg35kj5w9dTVY');

$link = Paymongo::paymentLinks()->update('plink_uSJXoxTBNqRrg35kj5w9dTVY', [
    'description' => 'Invoice #1234 (rev 2)',
]);
```

`update()` sends a flat `PATCH`; the updatable fields include `status` (`active` / `archived`).

## Archive and unarchive

Shorthands for `update()` with a `status`:

```php
$link = Paymongo::paymentLinks()->archive('plink_uSJXoxTBNqRrg35kj5w9dTVY');   // status: archived
$link = Paymongo::paymentLinks()->unarchive('plink_uSJXoxTBNqRrg35kj5w9dTVY'); // status: active
```

## List

```php
$page = Paymongo::paymentLinks()->list(['limit' => 10]); // CursorPage<PaymentLink>

foreach ($page as $link) {
    // ...
}

// Every payment link, all pages, lazily:
Paymongo::paymentLinks()->list()->lazy()->each(function ($link) {
    // ...
});
```

Supported list parameters: `limit`, `before`, `after`.

## Payments made through a link

```php
$page = Paymongo::paymentLinks()->payments('plink_uSJXoxTBNqRrg35kj5w9dTVY'); // CursorPage<Payment>

foreach ($page as $payment) {
    $payment->status; // ?PaymentStatus
    $payment->money()->format();
}
```

Items here are standard `Luigel\Paymongo\Data\Payment` resources.

## Refund a link's payments

```php
$result = Paymongo::paymentLinks()->refund('plink_uSJXoxTBNqRrg35kj5w9dTVY', [
    'amount' => 150050,
]);
```

:::caution
PayMongo does not document this endpoint's response shape, so `refund()` returns the raw `data` payload as a plain array rather than a DTO. Expect the signature to tighten once the shape is verified upstream.
:::

## The PaymentLink DTO

Because the API returns flat objects, `PaymentLink` does not extend the shared `Resource` base:

```php
$link->id;              // ?string
$link->amount;          // ?int centavos — plus $link->money()
$link->currency;        // ?string
$link->description;     // ?string
$link->remarks;         // ?string
$link->status;          // ?PaymentLinkStatus (Active | Archived)
$link->livemode;        // ?bool
$link->url;             // ?string
$link->referenceNumber; // ?string
$link->metadata;        // ?array
$link->restrictions;    // ?array
$link->createdAt;       // ?CarbonImmutable — properties, parsed from the ISO 8601 strings
$link->updatedAt;       // ?CarbonImmutable

$link->raw;                                          // the full flat payload
$link->attribute('restrictions.completed_sessions'); // dot-notation access into it
```
