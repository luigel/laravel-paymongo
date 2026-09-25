# Changelog

All notable changes to `laravel-paymongo` will be documented in this file

## 3.0.0-dev (unreleased)

Complete rewrite. See [UPGRADE.md](UPGRADE.md) for the full v2 to v3 migration guide.

### Added
- Per-resource services on the `Paymongo` facade (`paymentIntents()`, `paymentMethods()`, `payments()`, `refunds()`, `webhooks()`, `checkoutSessions()`, `links()`, `paymentLinks()`, `qrph()`, `customers()`, `plans()`, `subscriptions()`, `payouts()`, deprecated `sources()`) returning typed, immutable DTOs with enum-typed fields.
- Subscriptions support: plans CRUD plus subscription create, list, cancel, change plan, change payment method, and test-cycle trigger.
- QR Ph support (`Paymongo::qrph()`): dynamic and static MPM QR codes on the v3 QR API (`generate`, `execute`, `retrieve` with QR string/image flags, `expire`) plus static in-store QR Ph codes via the v1 endpoint (`generateStatic`).
- Payment Links support (`Paymongo::paymentLinks()`) for the newer `/payment_links` API — flat request bodies, ISO 8601 timestamps, `active`/`archived` management status: `create`, `retrieve`, `update`, `archive`/`unarchive`, `list`, per-link `payments`, and `refund` (raw array; the response shape is undocumented upstream). The legacy `/links` API stays available unchanged as `Paymongo::links()`.
- Read-only Payouts support (`Paymongo::payouts()`): `list` with filters (`payout_status`, `provider`, `created_at.between`, `search`, `sort_by`, `order`), `retrieve`, per-payout `transactions`, and merchant payout `schedule`.
- `CursorTokenPage` for the Payouts API's token pagination: `nextCursor`/`prevCursor`, totals `meta`, `nextPage()`, `lazy()`, iteration, and counting.
- Flat-body client methods `postFlat()`/`patchFlat()` and `ClientConfig::origin()` for the newer APIs that skip the `data.attributes` envelope (payment links, v3 QR).
- Payment intent `capture()` (full and partial) and `retrieveUsingClientKey()` (public-key retrieval).
- Cursor pagination for list endpoints: `CursorPage` with `nextPage()`, `lazy()`, iteration, and counting.
- `Money` value object for integer-centavo amounts (`toDecimal()`, `format()`, arithmetic) and a `money()` helper on amount-bearing resources.
- Exception tree rooted at `PaymongoException` (`AuthenticationException`, `PaymentDeclinedException`, `ResourceNotFoundException`, `RateLimitException` with `retryAfter`, `ServerException`, `InvalidRequestException`, `ConnectionException`, `InvalidWebhookSignatureException`) carrying parsed `ApiError`s.
- Automatic `Idempotency-Key` on POST requests and automatic retries (429/5xx/connection errors) for idempotent requests; configurable via `paymongo.http.*` and `paymongo.idempotency.*`.
- First-class inbound webhooks: `Route::paymongoWebhooks()` macro, `paymongo.signature` middleware verifying the `Paymongo-Signature` header (with timestamp tolerance), cache-based event deduplication, and dispatched Laravel events — generic `WebhookReceived` plus a typed event class for every one of the 25 webhook event types.
- Multi-account support via `Paymongo::withSecretKey()`.
- Testing utilities: `Paymongo::fake()`, `Paymongo::assertSent()`, `Paymongo::assertNothingSent()`, and `Luigel\Paymongo\Testing\Fixtures` factories for every resource (including `paymentLink`, `mpmQr`, `qrExecution`, `staticQr`, `payout`, `payoutTransaction`, `payoutSchedule`, and the `flatList`/`payoutList` envelopes); the fake covers the entire API origin, `/v3` QR endpoints included, and interoperates with plain `Http::fake()`.
- Artisan commands `paymongo:webhook:create`, `paymongo:webhook:list`, `paymongo:webhook:toggle`.
- Opt-in contract test suite against the real test-mode API (`PAYMONGO_CONTRACT_TESTS=1`).
- Laravel Boost support: an AI guideline (`resources/boost/guidelines/core.blade.php`) loaded into agent sessions of apps depending on the package, a `paymongo-docs` skill that grounds PayMongo answers and payloads in the official docs, and a `paymongo-v3-upgrade` skill for migrating integrations from 2.x to 3.x.

### Changed
- Amounts are now integer centavos everywhere (v2 auto-converted float pesos; `amount_type` removed).
- Facade calls moved from fluent modules (`Paymongo::paymentIntent()->create()`) to services (`Paymongo::paymentIntents()->create()`); `find()` became `retrieve()`, `all()` became `list()`.
- Responses expose typed readonly properties instead of magic getters (`getStatus()` is now `->status`, an enum).
- Config file source moved from `config/config.php` to `config/paymongo.php` (publish tag `paymongo-config`) with a new shape: `base_url`, `http.*`, `idempotency.*`, and per-endpoint `webhooks.secret`/`webhooks.secrets` replacing per-event `webhook_signatures`.
- Webhook middleware parameter now names an endpoint secret (`paymongo.signature:orders`) instead of an event.
- HTTP is sent through Laravel's HTTP client (fakeable) instead of raw Guzzle.
- Requires PHP 8.2+ and Laravel 11–13.

### Removed
- Tokens API (`Paymongo::token()`), removed upstream by PayMongo.
- Magic getters (`getData()`, `get*()`), `BaseModel`, model-side actions (`$intent->cancel()`, `$link->archive()`, ...).
- `Paymongo::payment()->create()` — payments are created by attaching payment intents.
- v2 exceptions (`BadRequestException`, `UnauthorizedException`, `NotFoundException`, `PaymentErrorException`, `MethodNotFoundException`, `AmountTypeNotSupportedException`).
- Config keys `amount_type`, `version`, `signer`, `signature_header_name`, `webhook_signature(s)`.

### Security
- Removed the real test-mode API key that was committed to the repository; the test suite now runs fully faked with placeholder keys and `Http::preventStrayRequests()`, so no credentials live in the codebase.

## 2.4.0 (2023-04-30)

### Added
- Checkouts API
- Support laravel 10

## 2.3.0 (2022-12-15)

### Added
- Links API
- Customers API

### Fixed
- Failing tests

## 1.3.0 (2020-10-31)

### Added

-   Added `getData()` for all the models.
-   It can now access the properties of the data directly. (eg. `$token->id`)
-   Added magic methods to all the models to get the specific data. Using the `get` prefix for the method.

#### Example:

```php
// Get the ID of the token.
$id = $token->getId();

// Get the billing details of the payment method.
$paymentMethod->getBilling();

// Get the billing name of the payment method.
$paymentMethod->getBillingName();
```

-   Added artisan commands for webhooks.
