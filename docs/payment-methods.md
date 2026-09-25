---
title: Payment Methods
slug: payment-methods
order: 22
section: Accept payments
operations:
  - paymentMethods.create
  - paymentMethods.retrieve
---

# Payment Methods

A Payment Method is what the customer pays with: a card, an e-wallet, online banking, QR Ph, or buy now, pay later, along with their billing details. On its own it charges nothing. [Attach it to a payment intent](./payment-intents.md#attach-a-payment-method) to make the payment.

Every method lives on `Paymongo::paymentMethods()` and returns a [`PaymentMethod`](./reference/data-objects.md#paymentmethod). For every attribute PayMongo accepts, see its [Payment Method reference](https://docs.paymongo.com/reference/create-a-paymentmethod).

## Create a card payment method

`create()` takes the method's `type`, its `details`, and optional `billing`:

```php include=examples/payment-methods/create-card.php
```

In production, do not create card payment methods on your server. Create them in the browser with your **public** key, so card numbers never reach your server and you stay out of PCI DSS scope, then send the resulting `pm_...` id to your server to attach. The example above uses a test card, and is how your tests and scripts can create one.

## Create an e-wallet, bank, or QR Ph payment method

Every other type needs only its `type`. The customer authorizes the payment on the provider's page, or by scanning a code, after you attach it:

```php include=examples/payment-methods/create-e-wallet.php
```

`type` takes a `Luigel\Paymongo\Enums\PaymentMethodType` case or its string value:

| Case | Value | Pays with |
|:-----|:------|:----------|
| `Card` | `card` | Credit or debit card |
| `Gcash` | `gcash` | GCash |
| `Paymaya` | `paymaya` | Maya |
| `GrabPay` | `grab_pay` | GrabPay |
| `ShopeePay` | `shopee_pay` | ShopeePay |
| `Qrph` | `qrph` | Any bank or e-wallet app that scans QR Ph |
| `Dob` | `dob` | Direct online banking |
| `Brankas` | `brankas` | Online banking through Brankas |
| `Billease` | `billease` | BillEase, buy now, pay later |
| `Atome` | `atome` | Atome, buy now, pay later |

`expiry_seconds` sets how long a `qrph` or `shopee_pay` method can be paid once attached, from 60 to 9000 seconds for QR Ph.

## Retrieve a payment method

`retrieve()` returns the method with typed billing details. `$method->methodType` holds the `type`:

```php include=examples/payment-methods/retrieve.php
```
