<?php

namespace App\Listeners;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Luigel\Paymongo\Events\PaymentPaid;

class MarkOrderPaid implements ShouldQueue
{
    public function handle(PaymentPaid $event): void
    {
        $webhookEvent = $event->event; // Luigel\Paymongo\Webhooks\WebhookEvent

        $order = Order::where('reference', $webhookEvent->resourceAttribute('metadata.order_reference'))->first();

        // Not ours, already handled, or not the amount we asked for.
        if ($order === null || $order->paid_at !== null || $webhookEvent->resourceAttribute('amount') !== $order->amount) {
            return;
        }

        $order->payment_id = $webhookEvent->resourceId(); // "pay_..."
        $order->paid_at = $webhookEvent->timestamp ?? now();
        $order->save();
    }
}
