---
title: QR Ph
slug: qrph
order: 25
section: Accept payments
operations:
  - qrph.generate
  - qrph.retrieve
  - qrph.expire
  - qrph.execute
  - qrph.generateStatic
---

# QR Ph

QR Ph is the Philippine national QR code standard. A customer scans a QR Ph code with the app of any participating bank or e-wallet, such as GCash, Maya, or BPI, and pays. Nobody is redirected.

The package covers three uses of it:

| Use | For | Call | Confirmed by |
|:----|:----|:-----|:-------------|
| [At checkout](#take-a-qr-ph-payment-at-checkout) | An online payment for a known amount | `paymentIntents()` with the `qrph` method | `payment.paid` |
| [In store](#print-a-static-code-for-your-store) | A permanent code at a counter; the customer types the amount | `qrph()->generateStatic()` | Your PayMongo dashboard, and an SMS |
| [Wallet QR](#move-money-with-wallet-qr) | Moving money into or out of your PayMongo Wallet | `qrph()->generate()`, `execute()` | `qr.paid` |

To take payments, use the first two. Wallet QR is not a payment method: it moves money like a bank transfer.

## Take a QR Ph payment at checkout

A QR Ph payment at checkout is a [payment intent](./payment-intents.md) paid with the `qrph` payment method. Attaching the method returns a QR code for the intent's exact amount:

```php include=examples/qrph/checkout.php
```

1. Show the image in `next_action.code.image_url` on your page. It is a base64 image, ready for an `<img>` tag.
2. The customer scans it in their banking or e-wallet app and pays.
3. PayMongo sends `payment.paid`, dispatched as `Luigel\Paymongo\Events\PaymentPaid`.

The code is single-use and expires 30 minutes after you attach it, or after the payment method's `expiry_seconds` (60 to 9000). If it expires unpaid, PayMongo sends `qrph.expired`, dispatched as `QrphExpired`, and the intent is back to `awaiting_payment_method`, so you can attach a new code.

With a [Checkout Session](./checkout-sessions.md), add `qrph` to `payment_method_types` instead, and PayMongo's page shows the code.

## Print a static code for your store

`generateStatic()` creates a permanent code to print and put at a counter. The customer scans it and types in the amount they owe:

```php include=examples/qrph/generate-static.php
```

Payments to it show up in your PayMongo dashboard, and PayMongo texts each one to `mobile_number` if you set it. It returns a [`StaticQr`](./reference/data-objects.md#staticqr).

## Move money with Wallet QR

Wallet QR codes move money into or out of your PayMongo Wallet over QR Ph. They run on PayMongo's v3 QR API, which the package calls for you. See PayMongo's [Wallet QR](https://docs.paymongo.com/docs/money-movement-wallet-qr) guide.

### Receive money

`generate()` creates a code anyone can scan to send money to your Wallet. It returns an [`MpmQr`](./reference/data-objects.md#mpmqr):

```php include=examples/qrph/generate.php
```

- A `dynamic` code carries a fixed `transaction_amount` in centavos, can be paid once, and expires after `expiry_seconds`.
- A `static` code has no amount: whoever scans it types one in. It never expires unless you expire it.
- `mode` takes a `Luigel\Paymongo\Enums\QrMode` case: `P2p`, `P2b`, `P2m`, or `P2micro`. To receive into your Wallet, PayMongo expects `p2p`.

`retrieve()` fetches a code, leaving out the QR string and image unless you ask for them. `expire()` stops a code from being paid:

```php include=examples/qrph/retrieve-and-expire.php
```

PayMongo sends `qr.paid` when a code is paid and `qr.expired` when it expires, dispatched as `QrPaid` and `QrExpired`.

### Send money

`execute()` pays a scanned QR Ph code from your Wallet. It returns a [`QrExecution`](./reference/data-objects.md#qrexecution):

```php include=examples/qrph/execute.php
```

**This moves real money out of your Wallet.** The response only acknowledges the request. PayMongo sends the outcome as a webhook: `qr.paid` when the transfer succeeds, and `qr.expired` when it fails. See [Webhooks](./webhooks.md).
