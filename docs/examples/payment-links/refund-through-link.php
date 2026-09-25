<?php

use Luigel\Paymongo\Facades\Paymongo;

$refund = Paymongo::paymentLinks()->refund('plink_uSJXoxTBNqRrg35kj5w9dTVY', [
    'payment_id' => 'pay_i7tdqnmwdszWo5B4Xqk2ogX5',
    'amount' => 1500.50, // pesos here, unlike every other amount
    'reason' => 'requested_by_customer',
]);

$refund->amount; // 150050, back in centavos
