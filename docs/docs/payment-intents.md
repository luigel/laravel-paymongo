---
title: Payment Intents
slug: payment-intents
order: 21
section: Accept payments
operations:
  - paymentIntents.create
  - paymentIntents.retrieve
  - paymentIntents.retrieveUsingClientKey
  - paymentIntents.attach
  - paymentIntents.capture
  - paymentIntents.cancel
---

# Payment Intents

A Payment Intent tracks one payment from start to finish: you create it for an amount, attach a [payment method](./payment-methods.md) to it, and it moves through its statuses until the payment succeeds. Use it when you build the payment form yourself instead of sending the customer to a [Checkout Session](./checkout-sessions.md).

Every method lives on `Paymongo::paymentIntents()` and returns a [`PaymentIntent`](./reference/data-objects.md#paymentintent). For every attribute PayMongo accepts, see its [Payment Intent reference](https://docs.paymongo.com/reference/create-a-paymentintent).

The flow:

1. Create the intent on your server, for the amount and the payment methods you accept.
2. Create a payment method for what the customer pays with, and attach it to the intent.
3. If the intent comes back `awaiting_next_action`, redirect the customer to authorize the payment. PayMongo sends them back to your return URL.
4. Wait for the `payment.paid` webhook to confirm it.

## Create an intent

`create()` takes the intent's attributes:

```php include=../examples/payment-intents/create.php
```

- `amount` is integer centavos, at least `100` (PHP 1.00). `currency` is `PHP`.
- `payment_method_allowed` lists what the intent may be paid with: `card`, `gcash`, `paymaya`, `grab_pay`, `shopee_pay`, `qrph`, `dob`, `brankas`, or `billease`.
- `idempotencyKey:` makes a retried request return the same intent instead of creating a second one. Use a key unique to the order. Without it the package sends a random key per call.

## Attach a payment method

`attach()` attaches a payment method to the intent, and that starts the payment. Pass `returnUrl:`, the page PayMongo sends the customer back to after they authorize.

For a card, your frontend usually creates the payment method with your public key, so the card number never reaches your server, and sends you its id. A card that needs 3D Secure comes back `awaiting_next_action`:

```php include=../examples/payment-intents/attach-card.php
```

E-wallets, online banking, and buy now, pay later always need the customer to authorize on the provider's page, and `returnUrl:` is required for them. Their payment method takes only a type:

```php include=../examples/payment-intents/attach-e-wallet.php
```

When your frontend attaches with the public key instead, it passes the intent's `clientKey`. On the server, `attach()` also accepts `clientKey:`, but the secret key does not need it.

When the customer lands back on your return URL, retrieve the intent to show its status. Treat it as a hint only: the `payment.paid` webhook is the proof of payment.

## Retrieve an intent

`retrieve()` returns the intent with its status, its payments, and the last payment error:

```php include=../examples/payment-intents/retrieve.php
```

`retrieveUsingClientKey()` retrieves an intent the way a browser does, with your **public** key and the intent's `clientKey` instead of the secret key. It needs `PAYMONGO_PUBLIC_KEY` set and throws `AuthenticationException` without it:

```php include=../examples/payment-intents/retrieve-using-client-key.php
```

## Authorize now, capture later

Create the intent with `'capture_type' => 'manual'` to hold the amount on the customer's card without charging it. After the customer attaches a card and passes 3D Secure, the intent waits in `awaiting_capture` until `capture()` charges the full amount, or a smaller one in centavos:

```php include=../examples/payment-intents/capture.php
```

PayMongo releases a hold it has not captured after 7 days. Holds work for Visa and Mastercard only, and PayMongo must enable them on your account first. See PayMongo's [Hold then capture](https://docs.paymongo.com/docs/payment-acceptance-hold-then-capture) guide.

## Cancel an intent

`cancel()` cancels an intent that has not succeeded, such as a hold you decide not to capture. Nothing is charged:

```php include=../examples/payment-intents/cancel.php
```

## Statuses

`$intent->status` is a `Luigel\Paymongo\Enums\PaymentIntentStatus`:

| Case | Value | Meaning |
|:-----|:------|:--------|
| `AwaitingPaymentMethod` | `awaiting_payment_method` | Created, or the last attempt failed. Attach a payment method. |
| `AwaitingNextAction` | `awaiting_next_action` | The customer must authorize at `$intent->nextAction->url`. |
| `Processing` | `processing` | The provider is confirming the payment. |
| `AwaitingCapture` | `awaiting_capture` | Authorized with manual capture. Capture or cancel it. |
| `Succeeded` | `succeeded` | Paid. `$intent->payments` holds the payment. |
| `Cancelled` | `cancelled` | Cancelled. It cannot be paid. |

After a failed attempt, the intent goes back to `awaiting_payment_method` and `$intent->lastPaymentError` says why, so the customer can try another method on the same intent.

## Know when it was paid

PayMongo sends `payment.paid` when a payment succeeds and `payment.failed` when an attempt fails. The package dispatches them as `Luigel\Paymongo\Events\PaymentPaid` and `PaymentFailed`. See [Webhooks](./webhooks.md).
