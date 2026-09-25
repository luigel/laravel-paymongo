<?php

use Luigel\Paymongo\Enums\SubscriptionStatus;
use Luigel\Paymongo\Facades\Paymongo;

$subscription = Paymongo::subscriptions()->retrieve('sub_iEbGuGDrxPZoTg9r6BLbdCfV');

$subscription->status === SubscriptionStatus::Active;
$subscription->plan?->name;            // the plan, nested
$subscription->customerId;
$subscription->attribute('anchor_date');           // "2026-09-01", the first payment's date
$subscription->attribute('next_billing_schedule'); // "2026-12-01"
$subscription->latestInvoice['status'] ?? null; // "paid", "open", ...

foreach (Paymongo::subscriptions()->list()->lazy() as $subscription) {
    $subscription->status;
}
