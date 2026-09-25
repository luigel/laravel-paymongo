<?php

use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::paymentLinks()->retrieve('plink_uSJXoxTBNqRrg35kj5w9dTVY');

$link->status;    // ?PaymentLinkStatus: Active or Archived
$link->createdAt; // ?CarbonImmutable

$link = Paymongo::paymentLinks()->update('plink_uSJXoxTBNqRrg35kj5w9dTVY', [
    'description' => 'Invoice INV-1234 (revised)',
    'amount' => 175000,
]);
