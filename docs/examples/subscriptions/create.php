<?php

use Luigel\Paymongo\Enums\SubscriptionStatus;
use Luigel\Paymongo\Facades\Paymongo;

$subscription = Paymongo::subscriptions()->create(
    'cus_b9ENKVqcHBfQQmv26uDYDCsD',
    'plan_9VrvpRkkYqK6twbhuvcVTtjM',
);

$subscription->status === SubscriptionStatus::Incomplete; // until the first payment

// The first invoice's payment intent: take the first payment on it as usual.
$intentId = $subscription->attribute('latest_invoice.payment_intent.id');
