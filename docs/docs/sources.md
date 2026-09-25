---
title: Sources (deprecated)
slug: sources
order: 60
section: Legacy
---

# Sources (deprecated)

:::caution Deprecated
The PayMongo **Sources API is deprecated**. For GCash, GrabPay, and other e-wallets, use [payment intents](./payment-intents.md) with an e-wallet payment method and a `return_url` — the redirect happens through `$intent->nextAction->url` and the payment is created automatically on authorization.

`Paymongo::sources()` remains for existing integrations only.
:::

## Create

Amounts are integer centavos. `type` is `gcash` or `grab_pay`; both redirect URLs are required:

```php
use Luigel\Paymongo\Facades\Paymongo;

$source = Paymongo::sources()->create([
    'type' => 'gcash',
    'amount' => 150050,
    'currency' => 'PHP',
    'redirect' => [
        'success' => 'https://example.com/payments/success',
        'failed' => 'https://example.com/payments/failed',
    ],
]);

return redirect()->away($source->redirect->checkoutUrl);
```

## Retrieve

```php
$source = Paymongo::sources()->retrieve('src_hsJNpsRFU1LxgVbxW4YJHRs6');

$source->sourceType;         // ?PaymentMethodType — gcash or grab_pay
$source->status;             // "pending" | "chargeable" | "consumed" | ...
$source->redirect?->success;
$source->money()->format();  // "₱1,500.50"
```

## The legacy flow

A chargeable source still needs a payment created against it. Listen for the `source.chargeable` webhook (`Luigel\Paymongo\Events\SourceChargeable`) — but note that v3 provides no `payments()->create()`; completing this legacy flow requires a raw API call. That is intentional: migrate to the [payment intent workflow](./payment-intents.md) instead, where PayMongo creates the payment for you.
