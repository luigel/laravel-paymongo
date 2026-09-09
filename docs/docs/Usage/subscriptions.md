---
sidebar_position: 8
slug: /subscriptions
id: subscriptions
---

# Plans and Subscriptions

Recurring billing: define a plan (what and how often to charge), then subscribe a [customer](./customers.md) to it. New in v3.

Plans live on `Paymongo::plans()` (`Luigel\Paymongo\Data\Plan`); subscriptions on `Paymongo::subscriptions()` (`Luigel\Paymongo\Data\Subscription`).

## Plans

### Create

Amount is integer centavos per cycle; `interval` is one of the `Luigel\Paymongo\Enums\PlanInterval` cases (`weekly`, `monthly`, `yearly`), `interval_count` 1–10:

```php
use Luigel\Paymongo\Enums\PlanInterval;
use Luigel\Paymongo\Facades\Paymongo;

$plan = Paymongo::plans()->create([
    'name' => 'Pro',
    'description' => 'Pro tier, billed monthly',
    'amount' => 99900, // PHP 999.00
    'currency' => 'PHP',
    'interval' => PlanInterval::Monthly,
    'interval_count' => 1,
]);

$plan->id; // "plan_9VrvpRkkYqK6twbhuvcVTtjM"
```

Optional: `plan_type` (defaults to `scheduled`), `cycle_count` (minimum 2, for plans that end), `metadata`.

### Retrieve, update, list

```php
$plan = Paymongo::plans()->retrieve('plan_9VrvpRkkYqK6twbhuvcVTtjM');

$plan = Paymongo::plans()->update('plan_9VrvpRkkYqK6twbhuvcVTtjM', ['name' => 'Pro (monthly)']);

$page = Paymongo::plans()->list(['limit' => 10]); // CursorPage<Plan>
```

Plan properties: `name`, `description`, `amount` (+ `money()`), `currency`, `interval` (`?PlanInterval`), `intervalCount`, `planType`, `cycleCount`.

## Subscriptions

### Create

```php
$subscription = Paymongo::subscriptions()->create(
    'cus_b9ENKVqcHBfQQmv26uDYDCsD',
    'plan_9VrvpRkkYqK6twbhuvcVTtjM',
);

$subscription->status; // ?SubscriptionStatus — starts Incomplete until the first charge succeeds
```

A new subscription is `incomplete` until the customer authorizes the first payment; check `$subscription->latestInvoice` / `$subscription->setupIntent` attributes for the checkout to complete it, and listen for `subscription.activated`.

### Retrieve and list

```php
$subscription = Paymongo::subscriptions()->retrieve('sub_iEbGuGDrxPZoTg9r6BLbdCfV');

$subscription->customerId;
$subscription->planId;
$subscription->plan;                // ?Plan — the nested plan resource
$subscription->paymentMethodId;
$subscription->nextBillingSchedule; // ?CarbonImmutable

$page = Paymongo::subscriptions()->list(['limit' => 10]); // CursorPage<Subscription>
```

### Change plan or payment method

```php
$subscription = Paymongo::subscriptions()->changePlan('sub_iEbGuGDrxPZoTg9r6BLbdCfV', 'plan_hsJNpsRFU1LxgVbxW4YJHRs6');

$subscription = Paymongo::subscriptions()->changePaymentMethod(
    'sub_iEbGuGDrxPZoTg9r6BLbdCfV',
    'pm_wr98R2gwWroVxfkcNVZBuXg2',
    redirectUrl: route('billing.updated'), // where the customer lands after authorizing the new method
);
```

### Cancel

A reason is required — a `Luigel\Paymongo\Enums\CancellationReason` case (`too_expensive`, `missing_features`, `switched_service`, `unused`, `other`) or its string value:

```php
use Luigel\Paymongo\Enums\CancellationReason;

$subscription = Paymongo::subscriptions()->cancel('sub_iEbGuGDrxPZoTg9r6BLbdCfV', CancellationReason::Unused);

$subscription->cancelledAt;        // ?CarbonImmutable
$subscription->cancellationReason; // ?CancellationReason
```

### Trigger a test billing cycle

In test mode you can bill the next cycle immediately instead of waiting:

```php
Paymongo::subscriptions()->triggerTestCycle('sub_iEbGuGDrxPZoTg9r6BLbdCfV'); // void
```

## Statuses

`$subscription->status` is a `Luigel\Paymongo\Enums\SubscriptionStatus`: `Incomplete`, `IncompleteCancelled`, `Active`, `PastDue`, `Unpaid`, `Cancelled`.

## Webhook events

Subscriptions are webhook-driven — listen for `subscription.activated`, `subscription.past_due`, `subscription.unpaid`, `subscription.updated`, `subscription.invoice.paid`, and `subscription.invoice.payment_failed` (all have typed event classes) to keep your app in sync. See [Webhooks](./webhooks.md).
