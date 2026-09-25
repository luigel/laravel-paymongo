---
title: Errors
slug: errors
order: 62
section: Concepts
---

# Errors

Every request that does not succeed throws a subclass of `Luigel\Paymongo\Exceptions\PaymongoException`, chosen by the HTTP status PayMongo answered with. Catch the ones you can do something about, and let the rest reach your exception handler:

```php include=examples/errors/catch.php
```

| Exception | Status | What to do |
|:----------|:-------|:-----------|
| `InvalidRequestException` | 400, 403, 422, and any other 4xx not below | Fix the request: a missing or invalid attribute, or a resource in the wrong state. Retrying the same request fails the same way. |
| `AuthenticationException` | 401 | Check `PAYMONGO_SECRET_KEY`, and that the key belongs to the mode you expect. |
| `PaymentDeclinedException` | 402 | Ask the customer for another payment method. |
| `ResourceNotFoundException` | 404 | Check the id, and that it was created with the same key and mode. |
| `RateLimitException` | 429 | Slow down. `retryAfter` holds the seconds from PayMongo's `Retry-After` header, when it sends one. |
| `ServerException` | 5xx | PayMongo failed. Try again later. |
| `ConnectionException` | none | PayMongo could not be reached: DNS, TLS, or a timeout. `status` is `null`, and `getPrevious()` is Laravel's connection exception. |

The package retries a request that fails with a connection error, a 429, or a 5xx before throwing, when repeating it is safe. See [Idempotency & retries](./idempotency.md).

`InvalidWebhookSignatureException` is the one exception not thrown by an API request. It is thrown for a webhook delivery that fails signature verification, and the webhook route answers those with `401` itself. See [Webhooks](./webhooks.md#signature-verification).

## What PayMongo said

Every exception carries what PayMongo sent back:

- `status` is the HTTP status, an `int`, or `null` for a `ConnectionException`.
- `errors()` is every entry of PayMongo's `errors` array, as `Luigel\Paymongo\Data\ApiError` objects with a `code`, a `detail`, and, for an invalid attribute, its `attribute` name and JSON `pointer`. Any of them can be `null`.
- `firstError()` is the first of them, or `null`.
- `getMessage()` is the first error's `detail`. When PayMongo's response has no errors, it is `PayMongo request failed with status {status}.`, and when the body is not PayMongo's JSON, the body itself is the one error's `detail`. A `ConnectionException`'s message starts `Could not connect to PayMongo:`.

Branch on `code`, which is stable, rather than on `detail`, which is written for people. PayMongo lists its codes by product: [Payment Intent and Payment Method errors](https://docs.paymongo.com/docs/payment-acceptance-errors-pipm), [Refund errors](https://docs.paymongo.com/docs/payment-acceptance-errors-refund), and the payment method pages under [Errors](https://docs.paymongo.com/docs/payment-acceptance-errors).

## Failed payments do not always throw

A payment that is declined after a redirect, or fails while the customer authorizes it in their e-wallet or bank, does not throw anywhere: the request that started it had already succeeded. You learn about it from the resource's status and from the `payment.failed` webhook (`Luigel\Paymongo\Events\PaymentFailed`). PayMongo's [Key concepts](https://docs.paymongo.com/docs/payment-acceptance-key-concepts) explains that a payment intent has no failed status: it goes back to `awaiting_payment_method` so the customer can try another method, and its `last_payment_error` attribute, read with `$intent->attribute('last_payment_error')`, says why. See [Payment Intents](./payment-intents.md).

The [Exceptions reference](./reference/exceptions.md) shows every exception class.
