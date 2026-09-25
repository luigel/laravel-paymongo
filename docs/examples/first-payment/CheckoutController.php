<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Luigel\Paymongo\Facades\Paymongo;

class CheckoutController
{
    public function __invoke(Order $order): RedirectResponse
    {
        $session = Paymongo::checkoutSessions()->create([
            'line_items' => [
                [
                    'name' => $order->description,
                    'amount' => $order->amount, // integer centavos: 150050 = PHP 1,500.50
                    'currency' => 'PHP',
                    'quantity' => 1,
                ],
            ],
            'payment_method_types' => ['card', 'gcash', 'paymaya'],
            'reference_number' => $order->reference,
            'success_url' => route('orders.show', $order),
            'cancel_url' => route('orders.show', $order),
        ]);

        return redirect()->away($session->checkoutUrl);
    }
}
