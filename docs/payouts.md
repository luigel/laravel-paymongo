---
title: Payouts
slug: payouts
order: 32
section: After payment
operations:
  - payouts.list
  - payouts.retrieve
  - payouts.transactions
  - payouts.schedule
---

# Payouts

A Payout is PayMongo sending you the money your customers paid. PayMongo generates payouts on its own, from payments that have cleared, and sends them on your payout schedule, so this service only reads them: to reconcile a payout against your orders, or to show what is coming next.

Every method lives on `Paymongo::payouts()`, and a single payout comes back as a [`Payout`](./reference/data-objects.md#payout). For how clearing and schedules work, see PayMongo's [Payouts guide](https://docs.paymongo.com/docs/money-movement-payouts), and for every field, its [Payouts reference](https://docs.paymongo.com/reference/getpayoutlist).

## List payouts

`list()` returns a page of payouts. Every filter is optional:

```php include=examples/payouts/list.php
```

- `payout_status` is a `Luigel\Paymongo\Enums\PayoutStatus` case or its value.
- `created_at.between` takes two dates, `YYYY-MM-DD..YYYY-MM-DD`.
- `search` matches a payout id or merchant id. `provider` is `paymongo_central_hub` or `unionbank`.
- `limit` defaults to 20.

Payout lists page differently from every other list. Instead of `hasMore`, the page is a `Luigel\Paymongo\Pagination\CursorTokenPage`, with an opaque `nextCursor` token that is `null` on the last page, and `meta` with the totals PayMongo sends, such as `total_records`. Iterate it, call `nextPage()`, or `lazy()` to walk every page, just like a `CursorPage`.

## Retrieve a payout

`retrieve()` returns a payout with what went into it:

```php include=examples/payouts/retrieve.php
```

Every amount is integer centavos. `amount` is the gross, the other amounts are what was taken from it, and `netAmount` is what reaches your account. `money()` formats the net amount.

`$payout->status` is a `Luigel\Paymongo\Enums\PayoutStatus`. PayMongo's guide describes these:

| Case | Value | Meaning |
|:-----|:------|:--------|
| `OnHold` | `on_hold` | Paused, usually for a compliance or risk review. PayMongo emails you what to do. |
| `InTransit` | `in_transit` | Sent, on its way to your account. |
| `Deposited` | `deposited` | Credited to your account. |
| `Returned` | `returned` | Could not be delivered, usually because of wrong bank or wallet details. |

The API reference also lists `pending` and `cancelled` (`Pending` and `Cancelled`), which PayMongo's guide does not describe further.

## See what a payout paid for

`transactions()` returns the payments, refunds, disputes, and adjustments a payout adds up, to match against your orders:

```php include=examples/payouts/transactions.php
```

Each one is a [`PayoutTransaction`](./reference/data-objects.md#payouttransaction), and its page is a `CursorTokenPage` too. `transactionType()` says what kind it is.

## See what comes next

`schedule()` takes your organization id (`org_...`) and returns your payout schedule with the payouts lined up on it:

```php include=examples/payouts/schedule.php
```

It comes back as a [`PayoutSchedule`](./reference/data-objects.md#payoutschedule). The first entry in `lineup` is your next payout, and the second the one after it. Change the schedule itself in the PayMongo Dashboard.

