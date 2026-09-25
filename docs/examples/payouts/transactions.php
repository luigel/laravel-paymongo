<?php

use Luigel\Paymongo\Facades\Paymongo;

foreach (Paymongo::payouts()->transactions('po_2fdKBqNAKMvUXTUAvhZDdXbW', ['limit' => 50])->lazy() as $transaction) {
    $transaction->id;                // "pay_...", "ref_...", ...
    $transaction->transactionType(); // "payment", "refund", "dispute", "adjustment", ...
    $transaction->amount;            // centavos
    $transaction->fee;
    $transaction->netAmount;
}
