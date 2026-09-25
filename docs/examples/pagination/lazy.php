<?php

use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Enums\PaymentStatus;
use Luigel\Paymongo\Facades\Paymongo;

// Every payment on every page, fetching the next page only when you reach it.
$collected = Paymongo::payments()->list(['limit' => 100])
    ->lazy() // LazyCollection<int, Payment>
    ->filter(fn (Payment $payment): bool => $payment->status === PaymentStatus::Paid)
    ->sum(fn (Payment $payment): int => $payment->amount ?? 0); // centavos
