---
title: Upgrading from v2
slug: upgrading
order: 53
section: Integration
---

# Upgrading from v2

v3 is a rewrite. The v2 facade's fluent, stateful modules with magic getters are gone. In their place are one service per resource, typed read-only data objects, Laravel's HTTP client with automatic idempotency keys and retries, a webhook route that dispatches Laravel events, and fakes for your tests.

Three ideas drive the breaking changes:

- **Exact amounts.** You send the integer centavos PayMongo speaks. Nothing is converted from floats behind your back.
- **Types, not magic.** Read-only properties and enums instead of `__get` and `get*()`, so your IDE and static analysis see everything.
- **Laravel-native.** The `Http` client underneath, so it can be faked; events for webhooks; a route macro; current publish and command conventions.

## Requirements

| | v2 | v3 |
|:-|:---|:---|
| PHP | 8.2+ | 8.2+ (Laravel 13 requires 8.3+) |
| Laravel | 10 to 13 | 11 to 13 |

```bash
composer require luigel/laravel-paymongo:^3.0
```

## At a glance

| Area | v2 | v3 |
|:-----|:---|:---|
| Amounts | float pesos, converted for you (`1500.50`) | integer centavos, sent as they are (`150050`) |
| Facade | modules: `Paymongo::paymentIntent()->create()` | services: `Paymongo::paymentIntents()->create()` |
| Responses | magic getters (`getStatus()`, `getBillingName()`) | typed properties and enums (`->status`, `->billing?->name`) |
| Lists | `->all()` returns a Collection | `->list()` returns a `CursorPage` |
| Exceptions | `BadRequestException`, `UnauthorizedException`, ... | subclasses of `PaymongoException`, with the parsed PayMongo errors |
| Config | `config/config.php`, published as `paymongo.php` | `config/paymongo.php`, in a new shape |
| Webhook verification | `paymongo.signature:{event}`, a secret per event | `paymongo.signature[:{name}]`, a secret per endpoint |
| Receiving webhooks | your own controller | `Route::paymongoWebhooks()`, Laravel events, deduplication |
| Artisan commands | `paymongo:webhook`, `paymongo:list-webhooks`, `paymongo:toggle-webhook` | `paymongo:webhook:create`, `paymongo:webhook:list`, `paymongo:webhook:toggle` |
| Tokens API | deprecated | removed |
| Sources API | supported | deprecated, still available |

## 1. Send amounts in centavos

v2 took float pesos and multiplied them by 100 (the `amount_type` config). v3 sends amounts exactly as you pass them, so pass integer centavos, as the PayMongo API expects. Amounts in responses are integer centavos too:

```php include=examples/upgrading/amounts.php
```

Check every `amount` you send, including checkout `line_items[].amount`: multiply pesos by 100 and cast to `int`. See [Amounts & Money](./amounts-and-money.md). The `amount_type` config key, the `Paymongo::AMOUNT_TYPE_FLOAT` and `Paymongo::AMOUNT_TYPE_INT` constants, and `AmountTypeNotSupportedException` are gone.

## 2. Call the services

Each resource has a service on the facade, and methods take the id instead of acting on a model you `find()`-ed first.

| v2 | v3 |
|:---|:---|
| `Paymongo::paymentIntent()->create($payload)` | `Paymongo::paymentIntents()->create($attributes)` |
| `Paymongo::paymentIntent()->find($id)` | `Paymongo::paymentIntents()->retrieve($id)` |
| `$intent->attach($paymentMethodId, $returnUrl)` | `Paymongo::paymentIntents()->attach($id, $paymentMethodId, $returnUrl)` |
| `$intent->cancel()` | `Paymongo::paymentIntents()->cancel($id)` |
| none | `Paymongo::paymentIntents()->capture($id, $amount)` |
| none | `Paymongo::paymentIntents()->retrieveUsingClientKey($id, $clientKey)` |
| `Paymongo::paymentMethod()->create($payload)` | `Paymongo::paymentMethods()->create($attributes)` |
| `Paymongo::paymentMethod()->find($id)` | `Paymongo::paymentMethods()->retrieve($id)` |
| `Paymongo::payment()->create($payload)` | removed: PayMongo creates the payment when a payment intent, checkout session or link is paid |
| `Paymongo::payment()->find($id)` | `Paymongo::payments()->retrieve($id)` |
| `Paymongo::payment()->all()` | `Paymongo::payments()->list($params)` |
| `Paymongo::refund()->create($payload)` | `Paymongo::refunds()->create($attributes)` |
| `Paymongo::refund()->find($id)` | `Paymongo::refunds()->retrieve($id)` |
| `Paymongo::refund()->all()` | `Paymongo::refunds()->list($params)` |
| `Paymongo::webhook()->create(['url' => $url, 'events' => $events])` | `Paymongo::webhooks()->create($url, $events)` |
| `Paymongo::webhook()->find($id)` | `Paymongo::webhooks()->retrieve($id)` |
| `Paymongo::webhook()->all()` | `Paymongo::webhooks()->list()` |
| `$webhook->update($payload)` | `Paymongo::webhooks()->update($id, $attributes)` |
| `$webhook->enable()`, `$webhook->disable()` | `Paymongo::webhooks()->enable($id)`, `disable($id)` |
| `Paymongo::link()->create($payload)` | `Paymongo::links()->create($attributes)` |
| `Paymongo::link()->find($id)` | `Paymongo::links()->retrieve($id)` |
| `Paymongo::link()->find($referenceNumber)` | `Paymongo::links()->retrieveByReference($referenceNumber)` |
| none | `Paymongo::links()->list($params)` |
| `$link->archive()`, `$link->unarchive()` | `Paymongo::links()->archive($id)`, `unarchive($id)` |
| `Paymongo::customer()->create($payload)` | `Paymongo::customers()->create($attributes)` |
| `Paymongo::customer()->find($id)` | `Paymongo::customers()->retrieve($id)` |
| `$customer->update($payload)` | `Paymongo::customers()->update($id, $attributes)` |
| `$customer->delete()` | `Paymongo::customers()->delete($id)` |
| `$customer->paymentMethods()` | `Paymongo::customers()->paymentMethods($customerId)` |
| none | `Paymongo::customers()->deletePaymentMethod($customerId, $paymentMethodId)` |
| `Paymongo::checkout()->create($payload)` | `Paymongo::checkoutSessions()->create($attributes)` |
| `Paymongo::checkout()->find($id)` | `Paymongo::checkoutSessions()->retrieve($id)` |
| `$checkout->expire()` | `Paymongo::checkoutSessions()->expire($id)` |
| `Paymongo::source()->create($payload)` | `Paymongo::sources()->create($attributes)`, deprecated |
| `Paymongo::source()->find($id)` | `Paymongo::sources()->retrieve($id)`, deprecated |
| `Paymongo::token()->create($payload)`, `find($id)` | removed |

New in v3, with no v2 counterpart: [Payment Links](./payment-links.md), [QR Ph](./qrph.md), [Payouts](./payouts.md), [Plans & Subscriptions](./subscriptions.md), and [`Paymongo::withSecretKey()`](./multiple-accounts.md) for more than one account.

## 3. Read typed properties

v2's `getData()`, `__get` and `get*()` methods are gone. v3 returns data objects from `Luigel\Paymongo\Data` with read-only typed properties, and fields with a fixed set of values, such as statuses, are enums:

```php include=examples/upgrading/responses.php
```

A value the package does not know yet, such as a status PayMongo added after this release, reads as `null`. `->attribute('status')` always has the raw string. The [Data objects reference](./reference/data-objects.md) lists every property.

## 4. Catch the new exceptions

Every exception extends `Luigel\Paymongo\Exceptions\PaymongoException`, which carries the HTTP `->status` and PayMongo's parsed errors, `->errors()` and `->firstError()`. See [Errors](./errors.md).

| v2 | v3 |
|:---|:---|
| `BadRequestException` | `InvalidRequestException` (400, 403, 422 and any other 4xx) |
| `UnauthorizedException` | `AuthenticationException` (401) |
| `PaymentErrorException` | `PaymentDeclinedException` (402) |
| `NotFoundException` | `ResourceNotFoundException` (404) |
| `MethodNotFoundException` | removed: there are no magic calls to mistype |
| `AmountTypeNotSupportedException` | removed: amounts are always centavos |
| none | `RateLimitException` (429), `ServerException` (5xx), `ConnectionException` (PayMongo unreachable) |
| none | `InvalidWebhookSignatureException` (a webhook that fails verification) |

## 5. Republish the config

The config file moved from `config/config.php` to `config/paymongo.php`, and its publish tag changed. Delete your published `config/paymongo.php` and publish it again:

```bash
php artisan vendor:publish --tag=paymongo-config
```

| v2 key | v3 |
|:-------|:---|
| `secret_key`, `public_key`, `livemode` | unchanged |
| `version` | removed: the API version header is no longer sent |
| `amount_type` | removed: amounts are always centavos |
| `signer`, `signature_header_name` | removed: verification is built in, and always reads `Paymongo-Signature` |
| `webhook_signature`, `webhook_signatures.{event}` | a secret per endpoint: `webhooks.secret`, and `webhooks.secrets.{name}` for more |
| none | `base_url`, `http.timeout`, `http.retries`, `http.retry_delay`, `idempotency.auto` |
| none | `webhooks.tolerance`, `webhooks.dedupe.enabled`, `webhooks.dedupe.ttl`, `webhooks.dedupe.store` |

[Installation & configuration](./installation.md#configuration) lists their environment variables. Rename your webhook secrets, from one per event to one per endpoint:

```env
# v2
PAYMONGO_WEBHOOK_SIG=whsk_...
PAYMONGO_WEBHOOK_SIG_PAYMENT_PAID=whsk_...

# v3
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

## 6. Receive webhooks with one route

The middleware alias is still `paymongo.signature`, now `Luigel\Paymongo\Http\Middleware\VerifyWebhookSignature`, but its parameter means something else. In v2 it named an event, such as `paymongo.signature:payment_paid`, and you had a route and a controller action per event. In v3 it names an endpoint's secret. A PayMongo endpoint has one secret for all the events it receives, so most apps need one route, with no parameter:

```php include=examples/receiving-webhooks/routes.php
```

A second endpoint, with a secret of its own, gets a second route; see [More than one endpoint](./webhooks.md#more-than-one-endpoint).

Move each v2 controller action into a listener for its event, such as `Luigel\Paymongo\Events\PaymentPaid` or `PaymentFailed`. The route verifies each delivery and drops repeats before your listener runs. See [Webhooks](./webhooks.md).

## 7. Rename the artisan commands

| v2 | v3 |
|:---|:---|
| `paymongo:webhook` | `paymongo:webhook:create {url} {--event=*}` |
| `paymongo:list-webhooks` | `paymongo:webhook:list` |
| `paymongo:toggle-webhook {id} --enable/--disable` | `paymongo:webhook:toggle {id} --enable/--disable` |

`paymongo:webhook:create` no longer prompts. Pass the URL and events: `php artisan paymongo:webhook:create https://example.com/paymongo/webhook --event=payment.paid --event=payment.failed`.

## 8. Move off Tokens and Sources

PayMongo removed the Tokens API, and `Paymongo::token()` is gone with it. Card details become a [payment method](./payment-methods.md) instead.

The Sources API still works but PayMongo has deprecated it. v3 still charges a chargeable source with `payments()->create()` (see [Sources](./sources.md#charge-a-chargeable-source)), but move GCash and GrabPay to a payment intent with an e-wallet payment method. PayMongo creates the payment when the customer authorizes it, so there is no step for you to charge:

```php include=examples/upgrading/e-wallet.php
```

Listen for `payment.paid` (`PaymentPaid`) instead of `source.chargeable`. See [Sources](./sources.md) and [Payment Intents](./payment-intents.md).

## 9. Rewrite your tests

Tests that mocked Guzzle or the `Paymongo` class need rewriting. `Paymongo::fake()` answers every request without the network, `Paymongo::assertSent()` and `assertNothingSent()` check what was sent, and `Fixtures` builds any payload, webhook events included. See [Testing](./testing.md).
