<?php

use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->retrieve('pi_hsJNpsRFU1LxgVbxW4YJHRs6'); // v2: ->find()

$intent->status === PaymentIntentStatus::Succeeded; // v2: getStatus() === 'succeeded'
$intent->status?->value;                            // the raw string, when you need one
$intent->clientKey;                                 // v2: getClientKey()
$intent->attribute('payment_method_options.card.request_three_d_secure'); // any raw attribute
$intent->createdAt();                               // ?CarbonImmutable, not a unix int
$intent->toArray();                                 // v2: getData()
