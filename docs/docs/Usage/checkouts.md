---
sidebar_position: 6
slug: /checkout-sessions
id: checkout-sessions
---

# Checkout Sessions

A checkout session is a PayMongo-hosted payment page: you send line items and allowed payment method types, redirect the customer to the returned `checkoutUrl`, and PayMongo handles the rest.

All methods live on `Paymongo::checkoutSessions()` and return `Luigel\Paymongo\Data\CheckoutSession` DTOs. See the [PayMongo documentation](https://developers.paymongo.com/reference/checkout-session-resource) for payload guidelines.

## Create

Line item amounts are integer centavos **per unit**:

```php
use Luigel\Paymongo\Facades\Paymongo;

$session = Paymongo::checkoutSessions()->create([
    'line_items' => [
        [
            'name' => 'A payment card',
            'amount' => 10000,      // PHP 100.00 each
            'currency' => 'PHP',
            'quantity' => 2,
            'description' => 'Something of a product.',
            'images' => ['https://example.com/product.png'],
        ],
    ],
    'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'],
    'success_url' => 'https://example.com/checkout/success',
    'cancel_url' => 'https://example.com/checkout/cancel',
    'reference_number' => 'ORDER-1234',
    'description' => 'Order #1234',
    'send_email_receipt' => true,
    'show_line_items' => true,
    'metadata' => ['order_id' => '1234'],
]);

return redirect()->away($session->checkoutUrl);
```

Create accepts an optional idempotency key: `Paymongo::checkoutSessions()->create($attributes, idempotencyKey: $order->uuid)`.

## Retrieve

```php
$session = Paymongo::checkoutSessions()->retrieve('cs_CbFCTDfxvMFNjwjVi26Uzhtj');

$session->status;         // ?CheckoutSessionStatus (Active | Expired)
$session->checkoutUrl;
$session->referenceNumber;
$session->lineItems;      // list<LineItem> — each with money(), name, quantity, ...
$session->paymentIntent;  // ?PaymentIntent created behind the session
$session->payments;       // list<Payment> once paid
```

## Expire

Expire an active session so it can no longer be paid:

```php
$session = Paymongo::checkoutSessions()->expire('cs_CbFCTDfxvMFNjwjVi26Uzhtj');
```

## Knowing when it was paid

Listen for the `checkout_session.payment.paid` webhook event (`Luigel\Paymongo\Events\CheckoutSessionPaymentPaid`) rather than polling — see [Webhooks](./webhooks.md).
