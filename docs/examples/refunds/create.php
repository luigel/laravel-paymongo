<?php

use Luigel\Paymongo\Enums\RefundReason;
use Luigel\Paymongo\Facades\Paymongo;

$refund = Paymongo::refunds()->create([
    'payment_id' => 'pay_i7tdqnmwdszWo5B4Xqk2ogX5',
    'amount' => 50050, // PHP 500.50 of a PHP 1,500.50 payment, in centavos
    'reason' => RefundReason::Others, // or Duplicate, Fraudulent
    'notes' => 'Order 1234: one item out of stock', // up to 255 characters
    'metadata' => ['order_id' => '1234'],
], idempotencyKey: 'order-1234-refund-1');

$refund->status; // ?RefundStatus: usually Pending at first
