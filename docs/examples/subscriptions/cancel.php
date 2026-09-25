<?php

use Luigel\Paymongo\Enums\CancellationReason;
use Luigel\Paymongo\Facades\Paymongo;

$subscription = Paymongo::subscriptions()->cancel('sub_iEbGuGDrxPZoTg9r6BLbdCfV', CancellationReason::TooExpensive);

$subscription->status;             // ?SubscriptionStatus: Cancelled
$subscription->cancelledAt;        // ?CarbonImmutable
$subscription->cancellationReason; // ?CancellationReason
