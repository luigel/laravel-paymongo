---
title: Idempotency & retries
slug: idempotency
order: 63
section: Concepts
---

# Idempotency & retries

A request can fail after PayMongo has already acted on it: the connection drops, or the response times out. Sending it again could then create a second payment intent or refund the customer twice. PayMongo guards against this with [idempotent requests](https://docs.paymongo.com/reference/idempotent-requests): a request sent with an `Idempotency-Key` header that PayMongo has seen in the last 24 hours returns the original result instead of acting again.

## Automatic keys and retries

The package sends a fresh `Idempotency-Key`, a UUID, with every `POST`. Because the key is the same on every attempt of one call, the package can retry a `POST` safely:

| Request | Idempotency key | Retried |
|:--------|:----------------|:--------|
| `POST`: create, attach, capture, cancel, expire, archive, enable, ... | A new UUID per call, or yours | Yes, while it has a key |
| `GET` and `DELETE`: retrieve, list, delete | None | Yes |
| `PUT` and `PATCH`: update, change plan, ... | None | No |

PayMongo documents idempotency for requests that create a resource. The package sends a key and retries every `POST` all the same, including actions such as `attach()` and `capture()`; set `PAYMONGO_RETRIES=1` if you would rather retry those yourself.

A retried request is repeated after a connection error, a 429, or a 5xx. `PAYMONGO_RETRIES` is the number of attempts in all, the first included: `2` by default, so one retry, and `1` for none. Attempts are `PAYMONGO_RETRY_DELAY` milliseconds apart (`200`). Any other failure throws straight away. After the last attempt, the package throws the exception for the last response; see [Errors](./errors.md).

Set `PAYMONGO_AUTO_IDEMPOTENCY=false` to stop sending keys. A `POST` without a key is then never retried, unless you pass a key yourself.

## Your own key

An automatic key only protects one call. When your own code runs twice, a queued job retried, a customer who clicks **Pay** twice, a request that times out in the browser, it makes a new call with a new key, and PayMongo acts twice. Pass `idempotencyKey:` with a key made from your order, so every attempt for that order sends the same one:

```php include=../examples/idempotency/order-key.php
```

`create()` takes `idempotencyKey:` on `paymentIntents()`, `checkoutSessions()`, `paymentLinks()` and `refunds()`, the calls where a repeat costs money or confuses a customer. Every other `POST` sends an automatic key.

- Make the key unique to one operation on one thing: `ORDER-1234-payment-intent`, `ORDER-1234-refund-1`. A second, deliberate refund of the same order needs a new key.
- Send the same attributes with the same key. PayMongo rejects a request whose key it has seen with different parameters.
- A key lasts 24 hours at PayMongo. After that the same key makes a new request.
- PayMongo only stores a result once it starts acting on a request. A request that failed validation stored nothing, so you can fix it and retry with the same key.

## Webhooks are delivered more than once too

The same applies in the other direction: PayMongo retries a webhook until your app answers `2xx`, so your listeners must handle an event twice without acting twice. See [Webhooks](./webhooks.md#write-listeners-that-survive-retries).
