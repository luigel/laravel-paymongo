<?php

namespace App\Listeners;

use App\Models\Order;
use Luigel\Paymongo\Events\CheckoutSessionPaymentPaid;

class FulfillOrder
{
    public function handle(CheckoutSessionPaymentPaid $event): void
    {
        $order = Order::where('reference', $event->event->resourceAttribute('reference_number'))->firstOrFail();

        $order->paid_at = now();
        $order->save();

        // Ship it, email a receipt, ...
    }
}
