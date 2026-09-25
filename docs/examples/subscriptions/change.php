<?php

use Luigel\Paymongo\Facades\Paymongo;

// From the next cycle on, bill another plan instead:
$subscription = Paymongo::subscriptions()->changePlan('sub_iEbGuGDrxPZoTg9r6BLbdCfV', 'plan_hsJNpsRFU1LxgVbxW4YJHRs6');

// Charge a new card from the next cycle on. The customer authenticates it first:
$subscription = Paymongo::subscriptions()->changePaymentMethod(
    'sub_iEbGuGDrxPZoTg9r6BLbdCfV',
    'pm_wr98R2gwWroVxfkcNVZBuXg2',
    redirectUrl: 'https://example.com/billing', // where they land after authenticating
);

$nextActionUrl = $subscription->setupIntent['next_action_url'] ?? null; // send them here
