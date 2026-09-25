---
title: Payments
slug: payments
order: 30
section: After payment
operations:
  - payments.retrieve
  - payments.list
---

# Payments

A Payment is the money a customer actually paid: its amount, PayMongo's fee, what you keep, and how they paid. You never create one. PayMongo creates it when a [payment intent](./payment-intents.md), [checkout session](./checkout-sessions.md), or [link](./payment-links.md) is paid, so this service only reads them.

Both methods live on `Paymongo::payments()`, and a single payment comes back as a [`Payment`](./reference/data-objects.md#payment). For every attribute PayMongo returns, see its [List all Payments reference](https://docs.paymongo.com/reference/list-all-payments).

## Retrieve a payment

`retrieve()` returns one payment. Take its id from the `payment.paid` webhook, or from `$intent->payments` on the intent it paid:

```php include=examples/payments/retrieve.php
```

- Every amount is integer centavos. `money()` wraps `amount` in a `Luigel\Paymongo\Support\Money` for display.
- How the customer paid (the card's brand and last four digits, or the e-wallet) is in the raw `source`, which `attribute()` reads by dot-notation key.

## Statuses

`$payment->status` is a `Luigel\Paymongo\Enums\PaymentStatus`:

| Case | Value | Meaning |
|:-----|:------|:--------|
| `Pending` | `pending` | Not paid yet. |
| `Paid` | `paid` | Paid. It can be [refunded](./refunds.md). |
| `Failed` | `failed` | The attempt failed. |
| `Refunded` | `refunded` | Refunded in full. |
| `PartiallyRefunded` | `partially_refunded` | Part of it was refunded. |

## List payments

`list()` returns a page of payments:

```php include=examples/payments/list.php
```

The page is a `Luigel\Paymongo\Pagination\CursorPage`. Iterate it for its payments, check `hasMore`, and call `nextPage()` for the next one, or `lazy()` to walk every page. `limit` defaults to 10. PayMongo also documents `status` and `created_at` filters on this endpoint, which `list()` passes through as they are.

## Know when a payment happens

Rather than polling `list()`, let PayMongo tell you. It sends `payment.paid` and `payment.failed`, which the package dispatches as `Luigel\Paymongo\Events\PaymentPaid` and `PaymentFailed`. See [Webhooks](./webhooks.md).
