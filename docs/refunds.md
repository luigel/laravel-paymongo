---
title: Refunds
slug: refunds
order: 31
section: After payment
operations:
  - refunds.create
  - refunds.retrieve
  - refunds.list
---

# Refunds

A Refund returns all or part of a paid payment to the customer's original payment method. Only a payment whose status is `paid` can be refunded. You can refund one payment several times, as long as the refunds together do not exceed what was paid.

Every method lives on `Paymongo::refunds()`, and a single refund comes back as a [`Refund`](./reference/data-objects.md#refund). For every attribute PayMongo accepts, see its [Refund reference](https://docs.paymongo.com/reference/refund-resource) and [Refunds guide](https://docs.paymongo.com/docs/payment-acceptance-refunds).

## Refund a payment

`create()` takes the payment to refund, the amount, and why:

```php include=examples/refunds/create.php
```

- `amount` is integer centavos, at least `100` (PHP 1.00). The full payment amount refunds it in full, and anything less refunds part of it.
- `reason` is required: a `Luigel\Paymongo\Enums\RefundReason` case (`Duplicate`, `Fraudulent`, or `Others`) or its string value. PayMongo's reference also lists `requested_by_customer`, which you can pass as a string. The enum does not have it yet, so `$refund->reason` reads `null` for it. Read the raw value with `$refund->attribute('reason')`.
- `notes` (up to 255 characters) and `metadata` are for you.
- `idempotencyKey:` makes a retried request return the same refund instead of refunding twice. Use a key unique to this refund of this order.

How long a refund may be made after the payment, whether partial refunds are allowed, and how soon the customer sees the money all depend on the payment method. Card refunds are allowed for 60 days, and UnionBank online banking payments cannot be refunded at all. See PayMongo's [Refunds guide](https://docs.paymongo.com/docs/payment-acceptance-refunds) for the table.

The money comes out of your upcoming payout. If that balance cannot cover the refund, the refund does not go through until it can. A refund of a payment already paid out to you is deducted from your next payout.

## Retrieve a refund

`retrieve()` returns a refund with its status:

```php include=examples/refunds/retrieve.php
```

`$refund->status` is a `Luigel\Paymongo\Enums\RefundStatus`:

| Case | Value | Meaning |
|:-----|:------|:--------|
| `Pending` | `pending` | Being processed. It rarely stays here for more than a few minutes. |
| `Succeeded` | `succeeded` | Refunded. |
| `Failed` | `failed` | Did not go through. Try the refund again. |

PayMongo also documents a `processing` status, which reads as `null` here. Like `pending`, it rarely lasts. Contact PayMongo support about a refund that stays in either.

## List refunds

`list()` returns a page of refunds. Pass `payment_id` for the refunds of one payment:

```php include=examples/refunds/list.php
```

The page is a `Luigel\Paymongo\Pagination\CursorPage`. Iterate it for its refunds, check `hasMore`, and call `nextPage()` for the next one, or `lazy()` to walk every page.

## When it fails

A refund PayMongo rejects throws a `Luigel\Paymongo\Exceptions\PaymongoException`, usually an `InvalidRequestException`, and `$e->firstError()?->code` says why. PayMongo's Refunds guide names `refund_amount_exceeds_payment` when the amount is more than is left to refund, `payment_not_refundable` when the payment is not `paid`, and `payment_not_found`. See PayMongo's [refund errors](https://docs.paymongo.com/docs/payment-acceptance-errors-refund) for the rest.

## Know when it went through

PayMongo sends `payment.refunded` and `payment.refund.updated`, which the package dispatches as `Luigel\Paymongo\Events\PaymentRefunded` and `PaymentRefundUpdated`. It also dispatches `refund.succeeded` as `RefundSucceeded` if PayMongo sends it, though PayMongo's webhook reference does not list it. See [Webhooks](./webhooks.md).
