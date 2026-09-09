---
sidebar_position: 7
slug: /links
id: links
---

# Links

A payment link is a shareable URL for a one-off payment — no code on the paying side needed.

All methods live on `Paymongo::links()` and return `Luigel\Paymongo\Data\Link` DTOs. See the [PayMongo documentation](https://developers.paymongo.com/reference/links-resource) for payload guidelines.

## Create

```php
use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::links()->create([
    'amount' => 150050, // PHP 1,500.50 in centavos
    'description' => 'Invoice #1234',
    'remarks' => 'laravel-paymongo',
]);

$link->checkoutUrl;     // share this with your customer
$link->referenceNumber; // short reference, e.g. "WTmSJbV"
```

## Retrieve

By id, or by the short reference number printed on the link:

```php
$link = Paymongo::links()->retrieve('link_wWaibr22CzEnficNhQNPUdoo');

$link = Paymongo::links()->retrieveByReference('WTmSJbV'); // ?Link — null when nothing matches

$link->status;   // ?LinkStatus (Unpaid | Paid | Archived)
$link->payments; // list<Payment> made against the link
$link->money()->format(); // "₱1,500.50"
```

## List

```php
$page = Paymongo::links()->list(['limit' => 10]); // CursorPage<Link>

foreach ($page as $link) {
    // ...
}

// Every link, all pages, lazily:
Paymongo::links()->list()->lazy()->each(function ($link) {
    // ...
});
```

Supported list parameters: `limit`, `before`, `after`.

## Archive and unarchive

Archived links can no longer be paid:

```php
$link = Paymongo::links()->archive('link_wWaibr22CzEnficNhQNPUdoo');
$link = Paymongo::links()->unarchive('link_wWaibr22CzEnficNhQNPUdoo');
```

## Knowing when it was paid

Listen for the `link.payment.paid` webhook event (`Luigel\Paymongo\Events\LinkPaymentPaid`) — see [Webhooks](./webhooks.md).
