<?php

use Luigel\Paymongo\Facades\Paymongo;

$session = Paymongo::checkoutSessions()->create([
    'line_items' => [
        [
            'name' => 'Ceramic mug',
            'amount' => 45000, // PHP 450.00 per unit, in centavos
            'currency' => 'PHP',
            'quantity' => 2,
            'description' => 'Hand-glazed, 350 ml',
            'images' => ['https://example.com/images/mug.png'],
        ],
    ],
    'payment_method_types' => ['card', 'gcash', 'paymaya', 'qrph'],
    'reference_number' => 'ORDER-1234',
    'description' => 'Order ORDER-1234',
    'success_url' => 'https://example.com/orders/1234',
    'cancel_url' => 'https://example.com/orders/1234',
    'send_email_receipt' => true,
    'show_line_items' => true,
    'metadata' => ['order_id' => '1234'],
], idempotencyKey: 'order-1234-checkout');

return redirect()->away($session->checkoutUrl);
