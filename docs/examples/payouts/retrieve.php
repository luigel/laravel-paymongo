<?php

use Luigel\Paymongo\Enums\PayoutStatus;
use Luigel\Paymongo\Facades\Paymongo;

$payout = Paymongo::payouts()->retrieve('po_2fdKBqNAKMvUXTUAvhZDdXbW');

$payout->status === PayoutStatus::Deposited;
$payout->amount;           // gross, in centavos
$payout->fee;              // deductions, in centavos
$payout->taxAmount;        // and taxAmount, refundAmount, disputeAmount,
$payout->adjustmentAmount; // adjustmentAmount: each in centavos
$payout->netAmount;        // what reaches your account
$payout->bankName;         // e.g. "BDO"
$payout->bankAccountNumber;
