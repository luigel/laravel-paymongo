---
sidebar_position: 1
slug: /payment-intents
id: payment-intents
---

# Payment Intents

A payment intent tracks one payment from creation through authorization to success, across card 3DS flows and e-wallet redirects alike. It is the primary way to charge with PayMongo.

All methods live on `Paymongo::paymentIntents()` and return `Luigel\Paymongo\Data\PaymentIntent` DTOs. Refer to the [PayMongo documentation](https://developers.paymongo.com/reference/the-payment-intent-object) for every accepted attribute.

## Create

Amounts are integer centavos (`150050` = PHP 1,500.50), minimum `100`.

```php
use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
    'payment_method_options' => [
        'card' => ['request_three_d_secure' => 'automatic'],
    ],
    'description' => 'Order #1234',
    'statement_descriptor' => 'LUIGEL STORE',
    'metadata' => ['order_id' => '1234'],
]);

$intent->id;        // "pi_hsJNpsRFU1LxgVbxW4YJHRs6"
$intent->clientKey; // pass to your frontend for client-side confirmation
```

Pass your own idempotency key to make retries safe end-to-end (one is auto-generated otherwise):

```php
$intent = Paymongo::paymentIntents()->create($attributes, idempotencyKey: $order->uuid);
```

## Retrieve

```php
$intent = Paymongo::paymentIntents()->retrieve('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
```

### Retrieve with a client key

For client-side status polling, authenticate with your **public key** and the intent's `client_key` instead of the secret key:

```php
$intent = Paymongo::paymentIntents()->retrieveUsingClientKey(
    'pi_hsJNpsRFU1LxgVbxW4YJHRs6',
    'pi_hsJNpsRFU1LxgVbxW4YJHRs6_client_...'
);
```

Requires `PAYMONGO_PUBLIC_KEY` to be configured; throws `AuthenticationException` otherwise.

## Attach a payment method

Attaching triggers the payment attempt:

```php
$intent = Paymongo::paymentIntents()->attach('pi_hsJNpsRFU1LxgVbxW4YJHRs6', 'pm_wr98R2gwWroVxfkcNVZBuXg2');
```

### E-wallets need a return URL

For `gcash`, `grab_pay`, `paymaya`, and other redirect-based methods (also `dob` and `billease`), pass `returnUrl` — where the customer lands after authorizing — then send them to the authorization page:

```php
use Luigel\Paymongo\Enums\PaymentIntentStatus;

$method = Paymongo::paymentMethods()->create(['type' => 'gcash']);

$intent = Paymongo::paymentIntents()->attach(
    'pi_hsJNpsRFU1LxgVbxW4YJHRs6',
    $method->id,
    returnUrl: route('checkout.complete'),
);

if ($intent->status === PaymentIntentStatus::AwaitingNextAction) {
    return redirect()->away($intent->nextAction->url);
}
```

On your return URL, retrieve the intent again and check `status` — but treat the `payment.paid` webhook as the source of truth (see [Webhooks](./webhooks.md)).

## Capture and cancel

Create the intent with `'capture_type' => 'manual'` to authorize first and capture later:

```php
// Full capture
$intent = Paymongo::paymentIntents()->capture('pi_hsJNpsRFU1LxgVbxW4YJHRs6');

// Partial capture (centavos)
$intent = Paymongo::paymentIntents()->capture('pi_hsJNpsRFU1LxgVbxW4YJHRs6', 100000);

// Cancel an unfinished intent
$intent = Paymongo::paymentIntents()->cancel('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
```

## Statuses

`$intent->status` is a `Luigel\Paymongo\Enums\PaymentIntentStatus`:

| Case | Value |
|---|---|
| `AwaitingPaymentMethod` | `awaiting_payment_method` |
| `AwaitingNextAction` | `awaiting_next_action` (redirect the customer to `$intent->nextAction->url`) |
| `AwaitingCapture` | `awaiting_capture` (manual capture type only) |
| `Processing` | `processing` |
| `Succeeded` | `succeeded` |
| `Cancelled` | `cancelled` |

Useful properties: `amount`, `currency`, `description`, `statementDescriptor`, `clientKey`, `captureType`, `paymentMethodAllowed`, `payments` (the resulting `Payment` DTOs), `nextAction`, `lastPaymentError`, `metadata`, plus `money()` for display.
