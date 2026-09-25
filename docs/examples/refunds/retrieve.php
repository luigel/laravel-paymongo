<?php

use Luigel\Paymongo\Enums\RefundStatus;
use Luigel\Paymongo\Facades\Paymongo;

$refund = Paymongo::refunds()->retrieve('ref_vPSqdAPD2pmtKj6Ac5SRfXjs');

$refund->status === RefundStatus::Succeeded;
$refund->reason;             // ?RefundReason
$refund->paymentId;          // "pay_..."
$refund->money()?->format(); // "₱500.50"
$refund->refundedAt();       // ?CarbonImmutable
$refund->attribute('payout_id'); // the payout it was deducted from, or null
