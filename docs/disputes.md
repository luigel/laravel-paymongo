---
title: Disputes
slug: disputes
order: 33
section: After payment
operations:
  - disputes.retrieve
  - disputes.list
---

# Disputes

A Dispute, or chargeback, is a card payment the cardholder's bank is reversing. It is not a refund: PayMongo holds the amount and a PHP 800 fee from your payout straight away, and you have 5 working days to answer with evidence. For the lifecycle and what to submit, read PayMongo's [Disputes & Chargebacks guide](https://docs.paymongo.com/docs/payment-acceptance-disputes).

The package reads disputes. Answering one, by submitting evidence or accepting it, happens in the PayMongo dashboard under **Transactions > Disputes**; PayMongo has no API for it.

Every method lives on `Paymongo::disputes()`, and a single dispute comes back as a `Luigel\Paymongo\Data\Dispute`. PayMongo's API reference does not document these endpoints yet. The typed attributes (`amount`, `currency`, `status`, `reason`) are the ones its dispute webhook events carry, and the rest of the payload is available through `attribute()`.

## Know when one is filed

PayMongo sends `dispute.created` when a dispute is filed and `dispute.resolved` when the bank decides it, and the package dispatches them as `Luigel\Paymongo\Events\DisputeCreated` and `DisputeResolved`. React to `DisputeCreated` quickly: stop fulfilling the order, and do not refund the payment, because a refund on a disputed payment can be lost twice. See [Webhooks](./webhooks.md).

## Retrieve a dispute

`retrieve()` returns one dispute. Take its id from the webhook:

```php include=examples/disputes/retrieve.php
```

## List disputes

`list()` returns a page of disputes. Iterate it, check `hasMore`, call `nextPage()`, or `lazy()` to walk every page:

```php include=examples/disputes/list.php
```

## When PayMongo refuses

An account without dispute access gets a `403` with the code `access_denied`, thrown as a `Luigel\Paymongo\Exceptions\InvalidRequestException`. Test-mode accounts get it too, so `Paymongo::fake()` and `Fixtures::dispute()` are how to test your handling. See [Testing](./testing.md).
