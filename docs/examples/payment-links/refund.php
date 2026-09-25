<?php

use Luigel\Paymongo\Enums\RefundReason;
use Luigel\Paymongo\Facades\Paymongo;

$refund = Paymongo::refunds()->create([
    'payment_id' => 'pay_i7tdqnmwdszWo5B4Xqk2ogX5', // a payment from payments() above
    'amount' => 150050, // centavos, like every other amount
    'reason' => RefundReason::Others,
]);
