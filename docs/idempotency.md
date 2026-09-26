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

| Failure | `GET`, `DELETE`, and `POST` with a key | `POST` without a key, `PUT`, `PATCH` |
|:--------|:---------------------------------------|:-------------------------------------|
| 429 Too Many Requests | Retried | Retried |
| Connection never opened: host not resolved, connection refused, TLS handshake failed | Retried | Retried |
| 5xx, or a timeout after the request was sent | Retried | Thrown |
| Any other 4xx | Thrown | Thrown |

A 429 and a connection that never opened both prove PayMongo did not act, so any request is retried after them. After a 5xx or a timeout PayMongo may have acted, so only a request that is safe to repeat is retried.

PayMongo documents idempotency for requests that create a resource. The package sends a key and retries every `POST` all the same, including actions such as `attach()` and `capture()`; set `PAYMONGO_RETRIES=0` if you would rather retry those yourself.

`PAYMONGO_RETRIES` is the number of retries after the first attempt: `2` by default, so up to three attempts, and `0` for none. The wait between attempts starts at `PAYMONGO_RETRY_DELAY` milliseconds (`200`), doubles on each retry, and is jittered to between half and all of that, so clients that failed together spread their retries out. No wait is longer than `PAYMONGO_MAX_RETRY_DELAY` (`5000`). When a 429 or 5xx carries a `Retry-After` header, the package waits that many seconds instead; a `Retry-After` longer than `PAYMONGO_MAX_RETRY_DELAY` is not waited for, and the `RateLimitException` is thrown at once with `retryAfter` set. After the last attempt, the package throws the exception for the last response; see [Errors](./errors.md).

A retried request blocks the PHP process while it waits. The longest a call can take is `PAYMONGO_TIMEOUT` for each attempt plus the waits between them: over 90 seconds with the defaults. Lower `PAYMONGO_TIMEOUT` or `PAYMONGO_RETRIES` for calls made during a web request, or make the call from a queued job.

A `DELETE` retried after a 5xx or a timeout may find the resource already gone, deleted by the attempt whose response was lost. The package treats that `404` as a successful delete.

Set `PAYMONGO_AUTO_IDEMPOTENCY=false` to stop sending keys. A `POST` without a key is then only retried after a 429 or a connection that never opened, unless you pass a key yourself.

## Your own key

An automatic key only protects one call. When your own code runs twice, a queued job retried, a customer who clicks **Pay** twice, a request that times out in the browser, it makes a new call with a new key, and PayMongo acts twice. Pass `idempotencyKey:` with a key made from your order, so every attempt for that order sends the same one:

```php include=examples/idempotency/order-key.php
```

`create()` takes `idempotencyKey:` on `paymentIntents()`, `checkoutSessions()`, `paymentLinks()` and `refunds()`, the calls where a repeat costs money or confuses a customer. Every other `POST` sends an automatic key.

- Make the key unique to one operation on one thing: `ORDER-1234-payment-intent`, `ORDER-1234-refund-1`. A second, deliberate refund of the same order needs a new key.
- Send the same attributes with the same key. PayMongo rejects a request whose key it has seen with different parameters.
- A key lasts 24 hours at PayMongo. After that the same key makes a new request.
- PayMongo only stores a result once it starts acting on a request. A request that failed validation stored nothing, so you can fix it and retry with the same key.

## Webhooks are delivered more than once too

The same applies in the other direction: PayMongo retries a webhook until your app answers `2xx`, so your listeners must handle an event twice without acting twice. See [Webhooks](./webhooks.md#write-listeners-that-survive-retries).
