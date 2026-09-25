---
title: Classic Links
slug: links
order: 24
section: Accept payments
operations:
  - links.create
  - links.retrieve
  - links.retrieveByReference
  - links.list
  - links.archive
  - links.unarchive
---

# Classic Links

Classic Links are PayMongo's original payment links API: a PayMongo-hosted payment page for one amount, at a URL you share with the customer. The package still supports them, on `Paymongo::links()`.

For new integrations, use [Payment Links](./payment-links.md) instead. PayMongo has announced that it will deprecate the Classic Links API and recommends moving existing integrations to Payment Links; see its [migration guide](https://docs.paymongo.com/reference/payment-links#migration-from-legacy-links).

A single link comes back as a [`Link`](./reference/data-objects.md#link).

## How they differ from Payment Links

| | Classic Links, `links()` | Payment Links, `paymentLinks()` |
|:--|:--|:--|
| Shareable URL | `$link->checkoutUrl` | `$link->url` |
| `status` | Whether it was paid: `Unpaid`, `Paid`, or `Archived` | Whether it takes payments: `Active` or `Archived` |
| Payments | On the link, as `$link->payments` | Fetched with `payments()` |
| Limit how many times it is paid | No | `restriction.completed_sessions.limit` |
| `metadata` | No | Yes |

## Create a link

`create()` takes the amount in integer centavos and a description. Send the customer the `checkoutUrl` it returns:

```php include=examples/links/create.php
```

## Retrieve a link

`retrieve()` finds a link by its id. `retrieveByReference()` finds it by the short reference number at the end of its URL, and returns `null` when no link has that reference:

```php include=examples/links/retrieve.php
```

## List links

`list()` returns a `CursorPage` of links. Iterate it, or call `lazy()` to walk every page:

```php include=examples/links/list.php
```

## Archive and unarchive a link

`archive()` stops a link from being paid, and `unarchive()` opens it again:

```php include=examples/links/archive.php
```

## Know when it was paid

PayMongo sends `link.payment.paid` when a customer pays a link, and the package dispatches it as `Luigel\Paymongo\Events\LinkPaymentPaid`. See [Webhooks](./webhooks.md).
