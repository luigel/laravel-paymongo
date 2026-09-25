---
title: Plans & Subscriptions
slug: subscriptions
order: 41
section: Recurring
operations:
  - plans.create
  - plans.retrieve
  - plans.update
  - plans.list
  - subscriptions.create
  - subscriptions.retrieve
  - subscriptions.list
  - subscriptions.changePlan
  - subscriptions.changePaymentMethod
  - subscriptions.cancel
  - subscriptions.triggerTestCycle
---

# Plans & Subscriptions

A Plan says what to charge and how often. A Subscription puts a [customer](./customers.md) on a plan, and PayMongo then bills them every cycle on its own: it issues an invoice, charges the customer's saved payment method, and retries when that fails.

Plans live on `Paymongo::plans()` and come back as a [`Plan`](./reference/data-objects.md#plan). Subscriptions live on `Paymongo::subscriptions()` and come back as a [`Subscription`](./reference/data-objects.md#subscription). For every attribute PayMongo accepts, see its [Subscriptions guide](https://docs.paymongo.com/docs/payment-acceptance-subscriptions) and its [Plan](https://docs.paymongo.com/reference/plan-resource) and [Subscription](https://docs.paymongo.com/reference/subscription-resource) references.

Subscriptions charge cards (Visa and Mastercard) and Maya. PayMongo must enable subscriptions on your account before you can test them.

The flow:

1. Create a plan once, and reuse it for every subscriber.
2. Create a customer for the subscriber.
3. Create a subscription for the customer on the plan. It starts `incomplete`.
4. Take the first payment on the subscription's first invoice, which saves the customer's payment method for the cycles after it.
5. Listen to webhooks to keep your app in step with each cycle.

## Create a plan

`create()` takes the plan's attributes:

```php include=examples/subscriptions/create-plan.php
```

- `name`, `description`, `amount`, `currency`, `interval`, and `interval_count` are required.
- `amount` is integer centavos per cycle, at least `100` (PHP 1.00).
- `interval` is a `Luigel\Paymongo\Enums\PlanInterval` case (`Weekly`, `Monthly`, or `Yearly`) or its value. `interval_count` is how many intervals make one cycle, from 1 to 10, so `Monthly` with 3 bills quarterly.
- `cycle_count` ends the subscription after that many payments, the first one included. It is at least 2. Leave it out to bill until the subscription is cancelled.
- `plan_type` defaults to `scheduled`, which PayMongo bills on the interval. An `on_demand` plan (Maya only) bills only when you ask PayMongo to, which the package does not wrap yet. See PayMongo's [Subscriptions guide](https://docs.paymongo.com/docs/payment-acceptance-subscriptions#on-demand-subscriptions).

## Retrieve, update, and list plans

`retrieve()` returns a plan, `update()` changes its `name`, `description`, `amount`, or `metadata`, and `list()` returns a page of plans:

```php include=examples/subscriptions/plans.php
```

The page is a `Luigel\Paymongo\Pagination\CursorPage`. Iterate it for its plans, check `hasMore`, and call `nextPage()` for the next one, or `lazy()` to walk every page.

## Subscribe a customer

`create()` takes the customer's id and the plan's id:

```php include=examples/subscriptions/create.php
```

PayMongo issues the subscription's first invoice straight away, with a payment intent on it. Take the payment on that intent the way you take any other: [attach](./payment-intents.md#attach-a-payment-method) a payment method, and send the customer to authorize it. Paying it saves the payment method for the next cycles and makes the subscription `active`.

The customer has 24 hours to pay. After that PayMongo cancels the subscription (`incomplete_cancelled`), and you create a new one to try again.

## Retrieve and list subscriptions

`retrieve()` returns a subscription with its plan, its latest invoice, and when it bills next. `list()` returns a page of subscriptions:

```php include=examples/subscriptions/retrieve-and-list.php
```

`latestInvoice` and `setupIntent` are the raw arrays PayMongo sends, with their own `id`, `status`, and `payment_intent` or `next_action_url`.

PayMongo sends `anchor_date` and `next_billing_schedule` as `YYYY-MM-DD` dates, which the `anchorDate` and `nextBillingSchedule` properties do not parse yet, so they read `null`. Read the dates with `attribute()`, as above.

## Change the plan or payment method

`changePlan()` moves the subscription to another plan from the next cycle on. The current cycle stays at the old plan's price. `changePaymentMethod()` charges a different payment method from the next cycle on:

```php include=examples/subscriptions/change.php
```

The customer authenticates a new card before it is used: PayMongo authorizes a small amount on it and then cancels that. `redirectUrl:` is where PayMongo sends them back afterwards, and it applies to cards only.

## Cancel a subscription

`cancel()` takes the subscription and a reason, a `Luigel\Paymongo\Enums\CancellationReason` case (`TooExpensive`, `MissingFeatures`, `SwitchedService`, `Unused`, or `Other`) or its value. PayMongo requires the reason:

```php include=examples/subscriptions/cancel.php
```

Cancelling takes effect immediately, and no new invoices follow. An invoice that is already open can still be paid.

## Test a billing cycle

In test mode, `triggerTestCycle()` bills the subscription's next cycle now, so you can test renewals and failed payments without waiting for the billing date. It returns nothing:

```php include=examples/subscriptions/test-cycle.php
```

## Statuses

`$subscription->status` is a `Luigel\Paymongo\Enums\SubscriptionStatus`:

| Case | Value | Meaning |
|:-----|:------|:--------|
| `Incomplete` | `incomplete` | Waiting for the first payment. |
| `IncompleteCancelled` | `incomplete_cancelled` | The first payment did not come within 24 hours. Create a new subscription. |
| `Active` | `active` | Every invoice is paid. Provide the service. |
| `PastDue` | `past_due` | The latest invoice's payment failed. PayMongo retries it once a day, up to 3 times. |
| `Unpaid` | `unpaid` | Still unpaid after 3 retries. Consider pausing the service. |
| `Cancelled` | `cancelled` | Cancelled. No new invoices. |

## Know when each cycle is billed

Every cycle happens on PayMongo's side, so webhooks are how your app hears about it. The package dispatches each as its own event in `Luigel\Paymongo\Events`:

| PayMongo event | Event class | When |
|:---------------|:------------|:-----|
| `subscription.activated` | `SubscriptionActivated` | The subscription became `active`. |
| `subscription.updated` | `SubscriptionUpdated` | The subscription changed. |
| `subscription.past_due` | `SubscriptionPastDue` | It became `past_due`: a cycle's payment failed. |
| `subscription.unpaid` | `SubscriptionUnpaid` | It became `unpaid`: the retries ran out. |
| `subscription.invoice.created` | `SubscriptionInvoiceCreated` | A cycle's invoice was created as a `draft`, a day before the billing date. |
| `subscription.invoice.finalized` | `SubscriptionInvoiceFinalized` | The invoice became `open`, and PayMongo attempts the charge. |
| `subscription.invoice.paid` | `SubscriptionInvoicePaid` | The invoice was paid. |
| `subscription.invoice.payment_failed` | `SubscriptionInvoicePaymentFailed` | The invoice's payment failed. |

PayMongo's [webhook reference](https://docs.paymongo.com/reference/create-a-webhook) lists every one of these except `subscription.activated`, which the package dispatches if PayMongo sends it. See [Webhooks](./webhooks.md).
