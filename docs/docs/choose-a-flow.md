---
title: Choose a flow
slug: choose-a-flow
order: 15
section: Choose a flow
---

# Choose a flow

PayMongo offers four ways to take a payment, and the package has a service for each. They differ in who builds the payment screen, whether the customer leaves your site, and which webhook event tells you the money arrived.

## At a glance

| Flow | Payment screen | Redirects | Confirms payment |
|:-----|:---------------|:----------|:-----------------|
| [Checkout Sessions](#checkout-sessions) | Hosted by PayMongo | Always: to `checkoutUrl`, then back to your `success_url` or `cancel_url` | `checkout_session.payment.paid` |
| [Payment Intents + Methods](#payment-intents--methods) | Embedded: you build it | Only for 3DS cards, e-wallets, online banking and BNPL: to `nextAction->url`, then back to your `return_url` | `payment.paid` |
| [Payment Links](#payment-links) | Hosted by PayMongo | None: you share the link, and the customer never comes back to your app | `link.payment.paid` |
| [QR Ph](#qr-ph) | Embedded: you show the QR code | None: the customer scans it in their bank or e-wallet app | `payment.paid` |

Each event arrives as a typed Laravel event from `Luigel\Paymongo\Events`: `CheckoutSessionPaymentPaid`, `PaymentPaid`, and `LinkPaymentPaid`. Whichever flow you use, the webhook event is the proof of payment. A customer reaching your `success_url` or `return_url` only means they came back; see [Webhooks](./webhooks.md).

## Checkout Sessions

You send line items and the payment methods to offer, and PayMongo hosts a complete payment page (`Paymongo::checkoutSessions()`). It handles 3DS and e-wallet authorization, so your app never sees card details.

1. Create the session and redirect the customer to `$session->checkoutUrl`.
2. The customer pays on PayMongo's page and is sent back to your `success_url` (or `cancel_url` if they back out).
3. `checkout_session.payment.paid` confirms the payment.

Pick it when you want to accept payments with the least code. [Your first payment](./your-first-payment.md) builds this flow end to end; [Checkout Sessions](./checkout-sessions.md) covers the rest.

## Payment Intents + Methods

The building blocks under every other flow. You build the payment screen yourself and drive the payment through its lifecycle with `Paymongo::paymentIntents()` and `Paymongo::paymentMethods()`.

1. Create a payment intent for the amount on your server.
2. Collect the customer's payment details and create a payment method, usually in the browser with your public key.
3. Attach the method to the intent. When the intent comes back as `awaiting_next_action`, redirect the customer to `$intent->nextAction->url` to authorize (a 3DS check, or the GCash or Maya page). PayMongo then sends them to your `return_url`.
4. `payment.paid` confirms the payment, and `payment.failed` reports a failed attempt.

Pick it when the payment must happen inside your own UI, or when you need the whole payment lifecycle in your hands, such as authorizing a card now and capturing it later. See [Payment Intents](./payment-intents.md) and [Payment Methods](./payment-methods.md).

## Payment Links

You create a link for an amount with `Paymongo::paymentLinks()` and share its URL wherever you talk to the customer. PayMongo hosts the payment page, and the customer gets an email receipt.

1. Create the link and share `$link->url`.
2. The customer opens it and pays. They are never redirected to your app, so there is no page of yours to return to.
3. `link.payment.paid` confirms the payment.

Pick it when there is no checkout on your site to send the customer to: invoices, and orders taken over chat, email, or social media. See [Payment Links](./payment-links.md). The older `/links` API is still available as `Paymongo::links()`; see [Links](./links.md).

## QR Ph

QR Ph is the Philippine national QR standard: any participating bank or e-wallet app can scan and pay the same code. For an online checkout, a QR Ph payment is a payment intent with the `qrph` method.

1. Create a payment intent with `qrph` in `payment_method_allowed`, create a `qrph` payment method, and attach it.
2. Show the QR code image from the attached intent, `$intent->attribute('next_action.code.image_url')`. The customer scans it in their app; nobody is redirected.
3. `payment.paid` confirms the payment. If the code is not paid within 30 minutes, `qrph.expired` (`QrphExpired`) fires instead.

Pick it for customers who pay from a banking app, and for paying in person from a screen. `Paymongo::qrph()` also generates a static code to print at a counter, and QR codes that move money into your PayMongo Wallet, confirmed by `qr.paid` (`QrPaid`). See [QR Ph](./qrph.md).

## Still deciding?

- **Least code, standard checkout:** Checkout Sessions.
- **Payment form inside your app:** Payment Intents + Methods.
- **No website, or a one-off request for payment:** Payment Links.
- **Scan to pay:** QR Ph.

Whichever you choose, handle its confirming event with a listener, as in [Your first payment](./your-first-payment.md).
