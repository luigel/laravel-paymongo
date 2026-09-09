---
sidebar_position: 11
slug: /payouts
id: payouts
---

# Payouts

Payouts are PayMongo depositing your collected balance to your bank account. The API is **read-only** — you inspect payouts, the transactions inside them, and your payout schedule. New in v3.

All methods live on `Paymongo::payouts()`. Payouts are standard `{id, type, attributes}` resources, but their lists paginate with **opaque cursor tokens** plus totals metadata instead of `has_more` — so `list()` and `transactions()` return a `CursorTokenPage`, not the usual `CursorPage`.

## List

```php
use Luigel\Paymongo\Enums\PayoutStatus;
use Luigel\Paymongo\Facades\Paymongo;

$page = Paymongo::payouts()->list([
    'payout_status' => PayoutStatus::Deposited,
    'provider' => 'paymongo_central_hub', // or unionbank
    'created_at.between' => '2026-08-01..2026-08-31', // YYYY-MM-DD..YYYY-MM-DD
    'search' => 'BDO',
    'sort_by' => 'net_amount', // created_at | net_amount
    'order' => 'desc',         // asc | desc
    'limit' => 20,             // default 20
]); // CursorTokenPage<Payout>
```

All parameters are optional; `after` / `before` take cursor tokens from a previous page.

### CursorTokenPage

```php
$page->items;      // list<Payout>
$page->nextCursor; // ?string — opaque token, null on the last page
$page->prevCursor; // ?string
$page->meta;       // totals: total_records, total_amount, total_per_currency (when present)
$page->first();    // ?Payout
count($page);      // items on this page

$next = $page->nextPage(); // ?CursorTokenPage — re-queries with after = nextCursor

// Every payout, all pages, lazily:
Paymongo::payouts()->list()->lazy()->each(function ($payout) {
    // ...
});
```

## Retrieve

```php
$payout = Paymongo::payouts()->retrieve('po_2fdKBqNAKMvUXTUAvhZDdXbW');

$payout->status;            // ?PayoutStatus (Pending | OnHold | InTransit | Deposited | Returned | Cancelled)
$payout->amount;            // gross centavos
$payout->netAmount;         // what actually lands in the bank
$payout->fee;
$payout->taxAmount;
$payout->refundAmount;
$payout->disputeAmount;
$payout->adjustmentAmount;
$payout->bankAccountName;
$payout->bankAccountNumber;
$payout->bankName;

$payout->money()->format(); // the net amount (falls back to gross), e.g. "₱4,855.00"
```

## Transactions inside a payout

The payments, refunds, disputes, and adjustments lined up in a payout:

```php
$page = Paymongo::payouts()->transactions('po_2fdKBqNAKMvUXTUAvhZDdXbW'); // CursorTokenPage<PayoutTransaction>

foreach ($page as $transaction) {
    $transaction->transactionType(); // payment | refund | dispute | adjustment | split_payment | split_refund
    $transaction->amount;            // ?int centavos — plus money()
    $transaction->netAmount;
    $transaction->fee;
}
```

Supported parameters: `limit`, `after`, `before`. A transaction's resource `type` *is* its kind, so `$transaction->type` carries the same value `transactionType()` returns (null-safely).

## Payout schedule

Pass your organization (merchant) id:

```php
$schedule = Paymongo::payouts()->schedule('org_9NxTZ8ZDVQpZC3bDMSKtwEXA'); // Luigel\Paymongo\Data\PayoutSchedule

$schedule->scheduleType; // e.g. "automatic" (from attributes.type)
$schedule->options;      // list<string> — the schedule kinds available to you
$schedule->lineup;       // the schedule lineup, e.g. payout days
```

## Knowing when money lands

Listen for the `payout.deposited` and `payout.returned` webhook events (typed classes `Luigel\Paymongo\Events\PayoutDeposited` / `PayoutReturned`) — see [Webhooks](./webhooks.md).
