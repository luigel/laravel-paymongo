<?php

use Luigel\Paymongo\Facades\Paymongo;

$session = Paymongo::checkoutSessions()->retrieve('cs_CbFCTDfxvMFNjwjVi26Uzhtj');

$session->status;          // ?CheckoutSessionStatus: Active or Expired
$session->referenceNumber; // "ORDER-1234"
$session->paymentIntent;   // ?PaymentIntent the session charges through

foreach ($session->lineItems as $item) {
    $item->name;
    $item->quantity;
    $item->money()?->format(); // "₱450.00", per unit
}

foreach ($session->payments as $payment) {
    $payment->id; // "pay_...", once the customer has paid
}
