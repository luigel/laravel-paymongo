<?php

use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::links()->retrieve('link_wWaibr22CzEnficNhQNPUdoo');

$link->status; // ?LinkStatus: Unpaid, Paid or Archived

foreach ($link->payments as $payment) {
    $payment->id; // "pay_..."
}

$linkByReference = Paymongo::links()->retrieveByReference('WTmSJbV'); // null when no link has it
