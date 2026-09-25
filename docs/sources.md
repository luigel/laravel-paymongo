---
title: Sources (deprecated)
slug: sources
order: 80
section: Legacy
operations:
  - sources.create
  - sources.retrieve
  - payments.create
---

# Sources (deprecated)

PayMongo has deprecated the Sources API, and its [Source Resource](https://docs.paymongo.com/reference/the-sources-object) reference says the Sources workflow is no longer supported. For GCash, GrabPay, and every other e-wallet, use a [payment intent](./payment-intents.md) with an e-wallet payment method, or a [checkout session](./checkout-sessions.md). `Paymongo::sources()` is kept only for integrations that still create sources.

A source was PayMongo's older way to take a GCash or GrabPay payment:

1. Create a source, and send the customer to its checkout URL.
2. The customer authorizes the payment in GCash or GrabPay. The source becomes `chargeable`, and PayMongo sends `source.chargeable`.
3. Create a payment from the chargeable source to take the money.

`payments()->create()` does step 3. With a payment intent, PayMongo creates the payment itself as soon as the customer authorizes, so there is no third step. [Upgrading from v2](./upgrading.md#8-move-off-tokens-and-sources) shows the replacement.

## Create a source

`create()` takes the e-wallet, the amount, and where to send the customer afterwards. It returns a `Luigel\Paymongo\Data\Source` (see the [Source reference](./reference/data-objects.md#source)):

```php include=examples/sources/create.php
```

- `type` is `gcash` or `grab_pay`.
- `amount` is integer centavos, at least `10000` (PHP 100.00).
- `redirect.success` and `redirect.failed` are both required. The customer returns to one of them after authorizing or failing.

## Retrieve a source

```php include=examples/sources/retrieve.php
```

`status` is a plain string, not an enum. PayMongo lists `pending`, `chargeable`, `cancelled`, `expired` and `paid`. A source is `pending` until the customer authorizes it, and `chargeable` after.

The package dispatches `source.chargeable` as `Luigel\Paymongo\Events\SourceChargeable`. See [Webhooks](./webhooks.md).

## Charge a chargeable source

A chargeable source takes no money until you create a payment from it. `payments()->create()` takes the amount, the currency, and the source, and returns a [`Payment`](./reference/data-objects.md#payment):

```php include=examples/sources/charge.php
```

- `amount` is integer centavos, at least `100`, and should be the source's amount.
- `source` is `['id' => $sourceId, 'type' => 'source']`.
- `idempotencyKey:` makes a retried request return the same payment instead of charging twice. Use a key unique to this order.

Charge a source when `SourceChargeable` arrives rather than when the customer returns to your success URL, and only once: PayMongo's [Create a Payment](https://docs.paymongo.com/reference/create-a-payment) reference lists `description`, `statement_descriptor`, and `metadata` as the other attributes.

For every attribute, see PayMongo's [Create a Source](https://docs.paymongo.com/reference/create-a-source) and [Source Resource](https://docs.paymongo.com/reference/the-sources-object) references.
