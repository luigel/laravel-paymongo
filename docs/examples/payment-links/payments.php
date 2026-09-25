<?php

use Luigel\Paymongo\Enums\PaymentStatus;
use Luigel\Paymongo\Facades\Paymongo;

$payments = Paymongo::paymentLinks()->payments('plink_uSJXoxTBNqRrg35kj5w9dTVY');

foreach ($payments as $payment) {
    if ($payment->status === PaymentStatus::Paid) {
        $payment->money()?->format(); // "₱1,500.50"
    }
}
