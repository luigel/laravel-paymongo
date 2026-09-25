---
title: Checkout Sessions
slug: checkout-sessions
order: 20
section: Accept payments
operations:
  - checkoutSessions.create
  - checkoutSessions.retrieve
  - checkoutSessions.expire
---

# Checkout Sessions

A Checkout Session is a payment page that PayMongo hosts for you. You send the line items and the payment methods to offer, redirect the customer to the session's `checkoutUrl`, and PayMongo takes the payment, including 3D Secure and e-wallet authorization. Your app never sees card details.

Every method lives on `Paymongo::checkoutSessions()` and returns a [`CheckoutSession`](./reference/data-objects.md#checkoutsession). For every attribute PayMongo accepts, see its [Checkout Session reference](https://docs.paymongo.com/reference/create_checkout_sessions).

New to Checkout Sessions? [Your first payment](./your-first-payment.md) builds the whole flow, from the **Pay** button to a fulfilled order.

## Create a session

`create()` takes the session's attributes. Redirect the customer to the `checkoutUrl` it returns:

```php include=../examples/checkout-sessions/create.php
```

- Each line item's `amount` is integer centavos **per unit**. The customer pays `amount × quantity` for each item.
- `payment_method_types` lists what the customer may pay with, such as `card`, `gcash`, `paymaya`, `grab_pay`, `qrph`, `dob`, and `billease`. Each must be enabled on your PayMongo account.
- `reference_number` and `metadata` come back on the session and on the webhook, which is how you find your order again.
- PayMongo sends the customer to `success_url` after paying and to `cancel_url` if they back out. Reaching `success_url` does not prove payment; the webhook does.
- `idempotencyKey:` makes a retried request return the same session instead of creating a second one. Without it the package sends a random key per call.

## Retrieve a session

`retrieve()` returns the session with its line items, the payment intent it charges through, and its payments once there are any:

```php include=../examples/checkout-sessions/retrieve.php
```

## Expire a session

`expire()` closes an active session, so its page can no longer be paid. Do this when the order it belongs to is cancelled or changed:

```php include=../examples/checkout-sessions/expire.php
```

## Know when it was paid

PayMongo sends `checkout_session.payment.paid` when the customer pays, and the package dispatches it as `Luigel\Paymongo\Events\CheckoutSessionPaymentPaid`. Fulfil the order in a listener for it, as in [Your first payment](./your-first-payment.md#4-fulfil-the-order), rather than on the `success_url` page. See [Webhooks](./webhooks.md) for registering the endpoint.
