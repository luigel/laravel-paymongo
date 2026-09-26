# Changelog

All notable changes to `laravel-paymongo` will be documented in this file

## v3.0.0 🎉 - 2026-09-26

### 🎉 Laravel PayMongo 3.0.0

The first stable release of v3: a complete rewrite with typed services, first-class webhooks and a fakeable HTTP layer. Coming from 2.x? 👉 Start with the [upgrade guide](https://github.com/luigel/laravel-paymongo/blob/3.x/UPGRADE.md).

```bash
composer require luigel/laravel-paymongo:^3.0

```
Requires PHP 8.2+ and Laravel 11 – 13.

#### ✨ Highlights

- 🧩 **Typed services for every resource.** `Paymongo::paymentIntents()`, `checkoutSessions()`, `paymentLinks()`, `qrph()`, `subscriptions()`, `payouts()`, `disputes()` and more return immutable DTOs with enum-typed fields.
- 💸 **Integer centavos everywhere**, with a `Money` value object for formatting and arithmetic.
- 📄 **Cursor pagination** through `CursorPage` / `CursorTokenPage`, with `nextPage()`, `lazy()` and iteration.
- 🪝 **First-class webhooks.** The `Route::paymongoWebhooks()` macro, signature verification middleware, deduplication, and a typed Laravel event for each of the 25 event types.
- 🔁 **Safe retries.** Automatic `Idempotency-Key`s, exponential backoff with jitter, and `Retry-After` support.
- 🚨 **A real exception tree** rooted at `PaymongoException`, carrying parsed `ApiError`s.
- 🧪 **Testing built in.** `Paymongo::fake()`, `assertSent()`, `assertNothingSent()`, and `Fixtures` factories for every resource.
- 🏦 **Multi-account support** through `Paymongo::withSecretKey($secret, $public)`.
- 🤖 **Laravel Boost support**, with an AI guideline plus `paymongo-docs` and `paymongo-v3-upgrade` skills.

#### 💥 Breaking changes from 2.x

- Amounts are integer centavos, and `amount_type` is gone.
- Fluent modules became services: `Paymongo::paymentIntent()->create()` → `Paymongo::paymentIntents()->create()`, `find()` → `retrieve()`, `all()` → `list()`.
- Typed readonly properties replace magic getters: `getStatus()` → `->status`.
- The config moved to `config/paymongo.php` with a new shape. Per-endpoint webhook secrets replace per-event signatures.
- The Tokens API and the v2 exceptions are removed.

The [upgrade guide](https://github.com/luigel/laravel-paymongo/blob/3.x/UPGRADE.md) covers every change.

#### 🆕 Since 3.0.0-beta.2

##### ➕ Added

- ⏱️ `http.max_retry_delay` (`PAYMONGO_MAX_RETRY_DELAY`, 5000 ms) caps any single wait between attempts.
- 🔀 Named webhook endpoints can override `paymongo.livemode` through `paymongo.webhooks.modes.{name}`.
- 🛡️ A successful response with malformed JSON or resource payloads throws `InvalidResponseException`.

##### 🔧 Changed

- 🔢 `http.retries` now counts retries after the first attempt. The default of `2` allows three attempts, and `0` disables retrying.
- 📈 Retries back off exponentially with jitter and honour `Retry-After`. A `Retry-After` longer than `http.max_retry_delay` throws `RateLimitException` at once.
- 🔁 Every request is retried after a 429 or a connection that never opened, since neither means PayMongo acted. That includes `PUT`, `PATCH` and a `POST` without an idempotency key.

##### 🐛 Fixed

- 🗑️ A `DELETE` retried after a 5xx or a timeout no longer throws `ResourceNotFoundException` when the lost attempt had already deleted the resource.
- 🪝 A failed webhook dispatch stays available for PayMongo to retry, and concurrent deliveries of an in-progress event get a `503`.
- 🔑 `withSecretKey()` no longer reuses the default account's public key. Pass the other account's key as `publicKey:`.

#### 📚 Docs

- 📖 [Documentation](https://github.com/luigel/laravel-paymongo/tree/3.x/docs)
- ⬆️ [Upgrade guide](https://github.com/luigel/laravel-paymongo/blob/3.x/UPGRADE.md)
- 📝 [Full changelog](https://github.com/luigel/laravel-paymongo/blob/3.x/CHANGELOG.md)

🙏 Thanks to everyone who tried the betas!

🤖 Generated with [Claude Code](https://claude.com/claude-code)

## 3.0.0 (2026-09-26)

First stable release of v3. See [UPGRADE.md](UPGRADE.md) to migrate from v2. Changes since `3.0.0-beta.2`:

### Added

- `http.max_retry_delay` (`PAYMONGO_MAX_RETRY_DELAY`, 5000 ms) caps any single wait between attempts.
- Named webhook endpoints can override `paymongo.livemode` through `paymongo.webhooks.modes.{name}`.
- Successful responses with malformed JSON or resource payloads throw `InvalidResponseException`.

### Changed

- `http.retries` (`PAYMONGO_RETRIES`) now counts retries after the first attempt, as its name says; it previously counted attempts in all. The default of `2` now allows three attempts, and `0` disables retrying.
- Retries back off exponentially with jitter, starting at `http.retry_delay`, instead of waiting a fixed delay, and wait for a `Retry-After` header when a 429 or 5xx sends one. A `Retry-After` longer than `http.max_retry_delay` is not waited for; the `RateLimitException` is thrown at once.
- Every request, `PUT`, `PATCH` and a `POST` without an idempotency key included, is now retried after a 429 or a connection that never opened, which prove PayMongo did not act.

### Fixed

- A `DELETE` retried after a 5xx or a timeout no longer throws `ResourceNotFoundException` when the lost attempt had already deleted the resource.
- A failed webhook event dispatch remains available for PayMongo to retry; concurrent deliveries of an in-progress event receive `503`.
- `withSecretKey()` no longer reuses the default account's public key. Pass the other account's key as `publicKey:` when using client-key retrieval.

## 3.0.0-beta.2 (2026-09-26)

### Added

- `payments()->create()` charges a chargeable source (`POST /v1/payments`), so the deprecated Sources flow can be finished with the package.
- Read-only Disputes support (`Paymongo::disputes()`): `retrieve` and cursor-paginated `list`, returning a `Dispute` with `amount`, `currency`, `reason`, and a `DisputeStatus` (`under_review`, `won`, `lost`, `expired`). PayMongo's reference does not document these endpoints yet; the typed attributes are those of its dispute webhook events, and accounts without dispute access get a 403 `access_denied`. `Paymongo::fake()` routes both and `Fixtures::dispute()` builds the payload.
- `webhooks()->delete()` removes a webhook endpoint (`DELETE /v1/webhooks/{id}`). PayMongo's reference does not list it yet; the API answers it with `204 No Content`.

### Changed

- `webhooks()->list()` returns a `CursorPage<Webhook>` and accepts `limit`, `before`, `after`, and `url`, matching PayMongo's paginated List all Webhooks endpoint. It previously returned a plain `list<Webhook>` of the first page only; iterate the page, or call `->lazy()` for every endpoint.
- `paymentLinks()->refund()` returns a typed `Refund` instead of the raw array, now that PayMongo documents the response as a standard refund resource. `Paymongo::fake()` answers it with a `Fixtures::refund()` payload.

## 3.0.0-beta.1 (2026-09-26)

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

- Added `getData()` for all the models.
- It can now access the properties of the data directly. (eg. `$token->id`)
- Added magic methods to all the models to get the specific data. Using the `get` prefix for the method.

#### Example:

```php
// Get the ID of the token.
$id = $token->getId();

// Get the billing details of the payment method.
$paymentMethod->getBilling();

// Get the billing name of the payment method.
$paymentMethod->getBillingName();

```
- Added artisan commands for webhooks.
