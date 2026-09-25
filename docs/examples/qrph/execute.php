<?php

use Luigel\Paymongo\Facades\Paymongo;

$execution = Paymongo::qrph()->execute([
    'qr_string' => '00020101021127580012com.p2pqrpay...', // decoded from the scanned code
    'amount' => 50000, // centavos; required even when the QR encodes the amount
    'reference_number' => 'PAYOUT-1234',
]);

$execution->id;     // "qrx_..."
$execution->status; // ?string, an acknowledgement only: the outcome arrives by webhook
