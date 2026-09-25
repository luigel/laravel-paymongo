---
title: Your first payment
slug: your-first-payment
order: 12
section: Start
---

# Your first payment

In this tutorial you take a test-mode payment for an order from start to finish:

1. The customer clicks **Pay** on their order, and your app creates a **Checkout Session** and sends them to PayMongo's hosted payment page.
2. They pay with a card or e-wallet, and PayMongo sends them back to your order page.
3. PayMongo calls your **webhook** with `checkout_session.payment.paid`, and your app **fulfils** the order.

It takes three files: a controller, two routes, and a listener. Every snippet below is a real file that the package's test suite runs, so it works as written.

## Before you start

- Install the package and add your **test** API keys, as in [Installation & configuration](./installation.md).
- Your app has an `Order` Eloquent model with a unique `reference` (such as `ORDER-1234`), a `description`, an integer `amount` in centavos, and a nullable `paid_at` timestamp.
- Your app has an order page named `orders.show` that takes the order.

## 1. Send the customer to checkout

Create a Checkout Session for the order and redirect the customer to its `checkoutUrl`, a payment page PayMongo hosts for you:

```php include=examples/first-payment/CheckoutController.php
```

- `amount` is **integer centavos** per unit, never a float: an order of PHP 1,500.50 is `150050`.
- `payment_method_types` lists what the customer may pay with. Each must be enabled on your PayMongo account.
- `reference_number` is your order's reference. It comes back on the webhook, which is how you will find the order again.
- PayMongo sends the customer to `success_url` after paying and to `cancel_url` if they back out. Reaching `success_url` is **not** proof of payment, since anyone can open that URL. The webhook in step 3 is.

## 2. Add the routes

In `routes/web.php`, route the **Pay** button to the controller, and register the webhook endpoint PayMongo will call:

```php include=examples/first-payment/routes.php
```

`Route::paymongoWebhooks()` registers `POST /paymongo/webhook`. It verifies each delivery's signature, skips deliveries it has already handled, and dispatches a Laravel event for each PayMongo event. CSRF protection is turned off for it automatically.

The **Pay** button on your order page posts to the new route:

```blade
<form method="POST" action="{{ route('orders.checkout', $order) }}">
    @csrf
    <button type="submit">Pay</button>
</form>
```

## 3. Register the webhook endpoint

PayMongo needs a public URL to call. Locally, expose your app with a tunnel such as `ngrok http 8000`, then register the endpoint for the `checkout_session.payment.paid` event:

```bash
php artisan paymongo:webhook:create https://your-tunnel.ngrok.app/paymongo/webhook --event=checkout_session.payment.paid
```

The command prints the endpoint's `secret_key`. PayMongo shows it only once, so put it in `.env` straight away:

```env
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

## 4. Fulfil the order

When the customer pays, PayMongo calls your endpoint and the package dispatches `Luigel\Paymongo\Events\CheckoutSessionPaymentPaid`. Listen for it in `app/Listeners/FulfillOrder.php`:

```php include=examples/first-payment/FulfillOrder.php
```

- Laravel discovers the listener from the type hint on `handle()`, so there is nothing to register.
- `$event->event->resourceAttribute()` reads a field of the checkout session PayMongo sent, by dot path. `resourceId()` is its `cs_...` id.
- The package has already verified that PayMongo sent the event and dropped repeat deliveries of it, so the listener only does your part.

The webhook can arrive a moment after the customer lands back on your order page. Show "Payment processing" there until `paid_at` is set.

## 5. Try it

1. Open an order and click **Pay**. You land on PayMongo's hosted checkout page.
2. Pay by card with `4343434343434345`, any future expiry date, and any CVC. Or choose GCash or Maya and click **Authorize** on PayMongo's test page.
3. PayMongo sends you back to the order page, and a moment later the order has a `paid_at`.

Order still unpaid? Check that the tunnel is still running and that `PAYMONGO_WEBHOOK_SECRET` matches the endpoint: a mismatched secret makes your app answer `401`, and the Webhooks page of the PayMongo dashboard lets you retry a failed delivery.

To cover this flow in your test suite without calling PayMongo, fake the API with `Paymongo::fake()` and post a signed event built by `Fixtures::event()`. See [Testing](./testing.md).

## Next steps

- [Choose a flow](./choose-a-flow.md): when a Checkout Session is the right fit, and when to use Payment Intents, Payment Links, or QR Ph instead.
- [Checkout Sessions](./checkout-sessions.md): line items, expiring a session, and reading its payments.
- [Webhooks](./webhooks.md): every event, multiple endpoints, and signature details.
