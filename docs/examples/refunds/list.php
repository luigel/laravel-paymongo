<?php

use Luigel\Paymongo\Facades\Paymongo;

$refunded = 0;

foreach (Paymongo::refunds()->list(['payment_id' => 'pay_i7tdqnmwdszWo5B4Xqk2ogX5']) as $refund) {
    $refunded += $refund->amount; // centavos
}
