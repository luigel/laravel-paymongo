---
title: Payment Links
slug: payment-links
order: 23
section: Accept payments
operations:
  - paymentLinks.create
  - paymentLinks.retrieve
  - paymentLinks.update
  - paymentLinks.archive
  - paymentLinks.unarchive
  - paymentLinks.list
  - paymentLinks.payments
  - paymentLinks.refund
---

# Payment Links

A Payment Link is a PayMongo-hosted payment page for one amount, at a URL you share wherever you talk to the customer: chat, email, social media, or an invoice. The customer pays with any method your account accepts, and gets an email receipt. There is no checkout on your site and no redirect back to it.

Every method lives on `Paymongo::paymentLinks()`, and a single link comes back as a [`PaymentLink`](./reference/data-objects.md#paymentlink). For every attribute PayMongo accepts, see its [Payment Links reference](https://docs.paymongo.com/reference/payment-links).

Payment Links are PayMongo's successor to [Classic Links](./links.md). Use them for new integrations.

## Create a link

`create()` takes the link's attributes. Send the customer the `url` it returns:

```php include=examples/payment-links/create.php
```

- `amount` is integer centavos, at least `100` (PHP 1.00). `currency` and `description` are required too.
- `restriction.completed_sessions.limit` is how many times the link can be paid, from 0 to 100. PayMongo defaults it to 1.
- `remarks` and `metadata` are for you. The customer sees the `description`.
- `idempotencyKey:` makes a retried request return the same link instead of creating a second one.

## Retrieve and update a link

`retrieve()` returns the link. `update()` changes its `amount`, `description`, or `remarks`:

```php include=examples/payment-links/retrieve-and-update.php
```

A link's `status` is whether it takes payments (`Active`) or not (`Archived`), not whether it was paid. To see what was paid, list its payments.

## Archive and unarchive a link

`archive()` stops a link from taking payments, and `unarchive()` opens it again:

```php include=examples/payment-links/archive.php
```

## List links

`list()` returns a page of links. Pass `status`, `reference_number`, or `mode` (`live` or `test`) to filter them:

```php include=examples/payment-links/list.php
```

The page is a `Luigel\Paymongo\Pagination\CursorPage`. Iterate it for its links, check `hasMore`, and call `nextPage()` for the next one, or `lazy()` to walk every page.

## See what a link was paid

`payments()` returns a page of the [payments](./reference/data-objects.md#payment) made through a link:

```php include=examples/payment-links/payments.php
```

## Refund a link's payment

Refund a payment made through a link as you would any other payment, with `Paymongo::refunds()`, which returns a typed `Refund`:

```php include=examples/payment-links/refund.php
```

`paymentLinks()->refund()` calls PayMongo's refund endpoint for payment links instead, and also returns a `Refund`. It takes a flat body of `payment_id`, `amount`, `reason`, and `metadata`, and PayMongo documents its `amount` in pesos rather than centavos, unlike every other amount (the `Refund` it returns is in centavos), so prefer `refunds()`:

```php include=examples/payment-links/refund-through-link.php
```

## Know when it was paid

PayMongo sends `link.payment.paid` when a customer pays a link, and the package dispatches it as `Luigel\Paymongo\Events\LinkPaymentPaid`. See [Webhooks](./webhooks.md).
