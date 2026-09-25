---
title: Multiple accounts
slug: multiple-accounts
order: 52
section: Integration
---

# Multiple accounts

The `Paymongo` facade uses the account whose secret key is in `PAYMONGO_SECRET_KEY`. When one app takes payments for several PayMongo accounts, such as a marketplace where each merchant has their own, call `Paymongo::withSecretKey()` for a copy that uses another account's key:

```php include=examples/multiple-accounts/with-secret-key.php
```

- `withSecretKey()` returns a new, separate `PaymongoManager` with every service, `paymentIntents()` through `payouts()`. Everything else, such as the timeout, retries and idempotency, comes from your config.
- The facade itself is unchanged, so one merchant's key never leaks into a request made for another. Keep the copy in a variable for as long as you work with that account.
- Store each merchant's secret key encrypted, for example with Laravel's `encrypted` cast.
- `Paymongo::fake()` fakes the copies too, and `Paymongo::assertSent()` sees their requests. Check which account a request was made for with its `Authorization` header, which is HTTP Basic auth with the secret key as the username.

## Webhooks for several accounts

Each account registers its own webhook endpoints, and each endpoint has its own secret. Register them with the copy for that account, for example `Paymongo::withSecretKey($key)->webhooks()->create(...)`, then give each endpoint a route with its own named secret. See [More than one endpoint](./webhooks.md#more-than-one-endpoint).

A named secret lives in `config/paymongo.php`, so this suits a handful of accounts known up front. For accounts that come and go, register a route of your own that finds the account from the URL, and check each delivery against that account's secret with `Luigel\Paymongo\Webhooks\SignatureVerifier`:

```php include=examples/receiving-webhooks/per-account-route.php
```

`verify()` throws an `InvalidWebhookSignatureException` for a delivery that fails the checks described under [Signature verification](./webhooks.md#signature-verification). Dispatching `WebhookReceived` hands the event to the same listeners as `Route::paymongoWebhooks()`. This route does not drop repeat deliveries, so make those listeners idempotent.
