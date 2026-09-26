# Upgrading from 2.x to 3.0

v3 is a from-scratch rewrite. The fluent, stateful `Paymongo::paymentIntent()->create()` chain with magic getters is gone; in its place are per-resource services returning typed, immutable DTOs, a native Laravel HTTP client with automatic idempotency and retries, first-class inbound webhook handling (signature middleware, events, dedupe), subscriptions support, and built-in testing fakes.

The philosophy behind the breaks:

- **Exactness over convenience** — amounts are the integer centavos PayMongo actually speaks; no silent float conversion.
- **Types over magic** — readonly properties and enums instead of `__get`/`__call`, so your IDE and static analysis see everything.
- **Laravel-native** — `Http` client under the hood (fakeable), events for webhooks, route macro, publishes and commands following current conventions.

## Requirements

| | v2 | v3 |
|---|---|---|
| PHP | 8.2+ | 8.2+ (Laravel 13 requires PHP 8.3+) |
| Laravel | 10 – 13 | 11 – 13 |

```bash
composer require luigel/laravel-paymongo:^3.0
```

## Breaking changes at a glance

| Area | v2 | v3 |
|---|---|---|
| Amounts | float pesos, auto-converted (`1500.50`) | integer centavos, sent as-is (`150050`) |
| Facade calls | fluent modules: `Paymongo::paymentIntent()->create()` | services: `Paymongo::paymentIntents()->create()` |
| Responses | magic getters (`getStatus()`, `getBillingName()`) | typed readonly properties + enums (`->status`, `->billing?->name`) |
| Listing | `->all()` returns a Collection | `->list()` returns a cursor-paginated `CursorPage` (`->lazy()` to walk all pages) |
| Exceptions | `BadRequestException`, `UnauthorizedException`, ... | `PaymongoException` tree with parsed `ApiError`s |
| Config | `config/config.php` (published as `paymongo.php`) | `config/paymongo.php`, new shape |
| Webhook verification | `paymongo.signature:{event_name}` against per-event secrets | `paymongo.signature[:{secret_name}]` against per-endpoint secrets |
| Inbound webhooks | bring your own controller | `Route::paymongoWebhooks()` + dispatched events + dedupe |
| Artisan commands | `paymongo:webhook`, `paymongo:list-webhooks`, `paymongo:toggle-webhook` | `paymongo:webhook:create`, `paymongo:webhook:list`, `paymongo:webhook:toggle` |
| Tokens API | deprecated | **removed** |
| Sources API | supported | **deprecated** (still available) |

## 1. Amounts: float pesos become integer centavos

v2 accepted floats and multiplied by 100 before sending (`amount_type` config). v3 sends amounts untouched: **always pass integer centavos**, exactly like the raw PayMongo API. Response amounts are integers too.

```php
// v2 — pesos as float, converted for you
$link = Paymongo::link()->create([
    'amount' => 1500.50,
    'description' => 'Invoice #1234',
]);
$link->getAmount(); // 1500.5 (float)

// v3 — centavos as int, sent as-is
$link = Paymongo::links()->create([
    'amount' => 150050,
    'description' => 'Invoice #1234',
]);
$link->amount;            // 150050
$link->money()->format(); // "₱1,500.50"  (Luigel\Paymongo\Support\Money)
$link->money()->toDecimal(); // "1500.50"
```

Audit every `amount` (and checkout `line_items[].amount`) you send: multiply pesos by 100 and cast to int. The `amount_type` config key, `Paymongo::AMOUNT_TYPE_FLOAT` / `Paymongo::AMOUNT_TYPE_INT` constants, and `AmountTypeNotSupportedException` are gone.

## 2. Facade method map

Services take the id as a parameter instead of operating on a previously `find()`-ed model. Complete map:

| v2 | v3 |
|---|---|
| `Paymongo::paymentIntent()->create($payload)` | `Paymongo::paymentIntents()->create($attributes)` |
| `Paymongo::paymentIntent()->find($id)` | `Paymongo::paymentIntents()->retrieve($id)` |
| `$intent->attach($paymentMethodId, $returnUrl)` | `Paymongo::paymentIntents()->attach($id, $paymentMethodId, $returnUrl)` |
| `$intent->cancel()` | `Paymongo::paymentIntents()->cancel($id)` |
| — (new) | `Paymongo::paymentIntents()->capture($id, ?int $amount)` |
| — (new) | `Paymongo::paymentIntents()->retrieveUsingClientKey($id, $clientKey)` |
| `Paymongo::paymentMethod()->create($payload)` | `Paymongo::paymentMethods()->create($attributes)` |
| `Paymongo::paymentMethod()->find($id)` | `Paymongo::paymentMethods()->retrieve($id)` |
| `Paymongo::payment()->create($payload)` | **removed** — payments are created by attaching payment intents (or via checkout sessions/links); the legacy source-to-payment flow is deprecated |
| `Paymongo::payment()->find($id)` | `Paymongo::payments()->retrieve($id)` |
| `Paymongo::payment()->all()` | `Paymongo::payments()->list($params)` |
| `Paymongo::refund()->create($payload)` | `Paymongo::refunds()->create($attributes)` |
| `Paymongo::refund()->find($id)` | `Paymongo::refunds()->retrieve($id)` |
| `Paymongo::refund()->all()` | `Paymongo::refunds()->list($params)` |
| `Paymongo::webhook()->create(['url' => $url, 'events' => $events])` | `Paymongo::webhooks()->create($url, $events)` |
| `Paymongo::webhook()->find($id)` | `Paymongo::webhooks()->retrieve($id)` |
| `Paymongo::webhook()->all()` | `Paymongo::webhooks()->list()` |
| `$webhook->update($payload)` / `Paymongo::webhook()->update($webhook, $payload)` | `Paymongo::webhooks()->update($id, $attributes)` |
| `$webhook->enable()` | `Paymongo::webhooks()->enable($id)` |
| `$webhook->disable()` | `Paymongo::webhooks()->disable($id)` |
| `Paymongo::link()->create($payload)` | `Paymongo::links()->create($attributes)` |
| `Paymongo::link()->find($id)` | `Paymongo::links()->retrieve($id)` |
| `Paymongo::link()->find($referenceNumber)` | `Paymongo::links()->retrieveByReference($referenceNumber)` |
| — (new) | `Paymongo::links()->list($params)` |
| `$link->archive()` / `Paymongo::link()->archive($link)` | `Paymongo::links()->archive($id)` |
| `$link->unarchive()` / `Paymongo::link()->unarchive($link)` | `Paymongo::links()->unarchive($id)` |
| `Paymongo::customer()->create($payload)` | `Paymongo::customers()->create($attributes)` |
| `Paymongo::customer()->find($id)` | `Paymongo::customers()->retrieve($id)` |
| `$customer->update($payload)` / `Paymongo::customer()->updateCustomer($customer, $payload)` | `Paymongo::customers()->update($id, $attributes)` |
| `$customer->delete()` / `Paymongo::customer()->deleteCustomer($customer)` | `Paymongo::customers()->delete($id)` |
| `$customer->paymentMethods()` / `Paymongo::customer()->getPaymentMethods($customer)` | `Paymongo::customers()->paymentMethods($customerId)` |
| — (new) | `Paymongo::customers()->deletePaymentMethod($customerId, $paymentMethodId)` |
| `Paymongo::checkout()->create($payload)` | `Paymongo::checkoutSessions()->create($attributes)` |
| `Paymongo::checkout()->find($id)` | `Paymongo::checkoutSessions()->retrieve($id)` |
| `$checkout->expire()` / `Paymongo::checkout()->expireCheckout($checkout)` | `Paymongo::checkoutSessions()->expire($id)` |
| `Paymongo::source()->create($payload)` | `Paymongo::sources()->create($attributes)` — deprecated, see [section 8](#8-tokens-removed-sources-deprecated) |
| `Paymongo::source()->find($id)` | `Paymongo::sources()->retrieve($id)` — deprecated |
| `Paymongo::token()->create($payload)` | **removed** — see [section 8](#8-tokens-removed-sources-deprecated) |
| `Paymongo::token()->find($id)` | **removed** |
| — (new) | `Paymongo::plans()->create() / retrieve() / update() / list()` |
| — (new) | `Paymongo::subscriptions()->create() / retrieve() / list() / cancel() / changePlan() / changePaymentMethod() / triggerTestCycle()` |
| — (new) | `Paymongo::withSecretKey($key)` — per-call account switching |

## 3. Reading responses: magic getters become typed properties

v2 models exposed `getData()`, `__get`, and `get*()` magic methods. v3 DTOs (`Luigel\Paymongo\Data\*`) expose readonly typed properties; enum-valued fields (status, currency, reason, ...) are native PHP enums.

```php
// v2
$intent = Paymongo::paymentIntent()->find('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
$intent->getStatus();               // "awaiting_payment_method" (string)
$intent->getClientKey();
$paymentMethod->getBillingName();   // magic nested getter
$intent->getData();                 // raw array

// v3
use Luigel\Paymongo\Enums\PaymentIntentStatus;

$intent = Paymongo::paymentIntents()->retrieve('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
$intent->status;                            // PaymentIntentStatus::AwaitingPaymentMethod (enum, nullable)
$intent->status === PaymentIntentStatus::Succeeded;
$intent->status?->value;                    // "succeeded" when you need the raw string
$intent->clientKey;
$paymentMethod->billing?->name;             // typed Billing / Address value objects
$paymentMethod->billing?->address?->city;
$intent->attributes;                        // raw attributes array
$intent->attribute('payment_method_options.card.request_three_d_secure'); // dot-notation raw access
$intent->toArray();                         // ['id' => ..., 'type' => ..., 'attributes' => [...]]
$intent->createdAt();                       // ?CarbonImmutable (was a unix int)
```

Unknown enum values (new API states this package predates) map to `null` — the raw string is always available via `->attribute('status')`.

## 4. Exceptions

All v3 exceptions extend `Luigel\Paymongo\Exceptions\PaymongoException` and carry `->status`, `->errors()` (`list<Luigel\Paymongo\Data\ApiError>` with `code`, `detail`, `pointer`, `attribute`), and `->firstError()`.

| v2 exception | v3 exception |
|---|---|
| `BadRequestException` | `InvalidRequestException` (400/403/422 and other unmapped 4xx) |
| `UnauthorizedException` | `AuthenticationException` (401) |
| `PaymentErrorException` | `PaymentDeclinedException` (402) |
| `NotFoundException` | `ResourceNotFoundException` (404) |
| `MethodNotFoundException` | removed — no magic calls to mistype |
| `AmountTypeNotSupportedException` | removed — amounts are always integer centavos |
| — (new) | `RateLimitException` (429, exposes `->retryAfter`) |
| — (new) | `ServerException` (5xx) |
| — (new) | `ConnectionException` (network failure) |
| — (new) | `InvalidWebhookSignatureException` (inbound webhook verification) |

## 5. Configuration

Delete your published `config/paymongo.php` and re-publish (the source moved from `config/config.php` to `config/paymongo.php`, and the publish tag changed):

```bash
php artisan vendor:publish --tag=paymongo-config
```

| v2 key | v3 |
|---|---|
| `secret_key`, `public_key`, `livemode` | unchanged |
| `version` | removed (the API version header is no longer sent) |
| `amount_type` | removed (centavos only) |
| `signer`, `signature_header_name` | removed (verification is built in, header is always `Paymongo-Signature`) |
| `webhook_signature` / `webhook_signatures.{event}` | replaced by **per-endpoint** secrets: `webhooks.secret` (default) and `webhooks.secrets.{name}` (named) |
| — (new) | `base_url` (`PAYMONGO_BASE_URL`) |
| — (new) | `http.timeout`, `http.retries`, `http.retry_delay`, `http.max_retry_delay` (`PAYMONGO_TIMEOUT`, `PAYMONGO_RETRIES`, `PAYMONGO_RETRY_DELAY`, `PAYMONGO_MAX_RETRY_DELAY`) |
| — (new) | `idempotency.auto` (`PAYMONGO_AUTO_IDEMPOTENCY`, default true — auto `Idempotency-Key` on every POST) |
| — (new) | `webhooks.tolerance` (`PAYMONGO_WEBHOOK_TOLERANCE`, signature timestamp drift, default 300s) |
| — (new) | `webhooks.dedupe.enabled` / `ttl` / `store` (`PAYMONGO_WEBHOOK_DEDUPE`, `PAYMONGO_WEBHOOK_DEDUPE_STORE`) |

Environment renames:

```env
# v2
PAYMONGO_WEBHOOK_SIG=whsk_...
PAYMONGO_WEBHOOK_SIG_PAYMENT_PAID=whsk_...

# v3 — one secret per endpoint, not per event
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

## 6. Webhook verification middleware

The alias is still `paymongo.signature` (class is now `Luigel\Paymongo\Http\Middleware\VerifyWebhookSignature`), but the **parameter changed meaning**: in v2 it named an event (`payment_paid`) keyed into `webhook_signatures`; in v3 it names an endpoint secret in `paymongo.webhooks.secrets`. One PayMongo endpoint has one `secret_key` regardless of how many events it subscribes to, so most apps need a single route with no parameter:

```php
// v2 — one route per event, per-event secrets, your own controller
Route::post('/payment-paid', 'PaymongoCallbackController@paymentPaid')
    ->middleware('paymongo.signature:payment_paid');
Route::post('/payment-failed', 'PaymongoCallbackController@paymentFailed')
    ->middleware('paymongo.signature:payment_failed');

// v3 — one route for all events; the package controller dispatches Laravel events
Route::paymongoWebhooks(); // POST /paymongo/webhook, name "paymongo.webhooks", CSRF-exempt

// v3 — second endpoint with its own secret (config: paymongo.webhooks.secrets.orders)
Route::paymongoWebhooks('webhooks/orders', 'orders');
```

Then move each v2 controller action into an event listener (`Luigel\Paymongo\Events\PaymentPaid`, `PaymentFailed`, `SourceChargeable`, ... — every event also dispatches the generic `Luigel\Paymongo\Events\WebhookReceived`). Duplicate deliveries are suppressed automatically via the cache.

## 7. Artisan commands

| v2 | v3 |
|---|---|
| `php artisan paymongo:webhook` | `php artisan paymongo:webhook:create {url} {--event=*}` |
| `php artisan paymongo:list-webhooks` | `php artisan paymongo:webhook:list` |
| `php artisan paymongo:toggle-webhook {id} --enable/--disable` | `php artisan paymongo:webhook:toggle {id} --enable/--disable` |

The create command takes the URL and events up front (no interactive prompts): `paymongo:webhook:create https://example.com/paymongo/webhook --event=payment.paid --event=payment.failed`.

## 8. Tokens removed, Sources deprecated

The **Tokens API** (`Paymongo::token()`) was removed by PayMongo and is gone from the package. Card details become payment methods now.

The **Sources API** (`Paymongo::sources()`) still works but is deprecated by PayMongo — new e-wallet integrations should use the payment intent workflow, which replaces the v2 source flow like this:

```php
// v2 (deprecated flow): create source -> redirect -> source.chargeable webhook -> create payment
$source = Paymongo::source()->create([
    'type' => 'gcash',
    'amount' => 1500.50,
    'currency' => 'PHP',
    'redirect' => ['success' => '...', 'failed' => '...'],
]);
// redirect to $source->getRedirect()['checkout_url'], then in the webhook:
Paymongo::payment()->create(['amount' => 1500.50, 'source' => ['id' => $source->id, 'type' => 'source'], ...]);

// v3: create intent -> attach gcash method with return_url -> redirect -> payment.paid webhook
$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['gcash'],
]);
$method = Paymongo::paymentMethods()->create(['type' => 'gcash']);
$intent = Paymongo::paymentIntents()->attach($intent->id, $method->id, returnUrl: route('checkout.complete'));

return redirect()->away($intent->nextAction->url); // PayMongo charges on authorization; listen for payment.paid
```

No manual `payment()->create()` step exists anymore — PayMongo creates the payment when the customer authorizes.

## 9. Testing

Your v2 tests that mocked Guzzle or the `Paymongo` class need rewriting — for the better: `Paymongo::fake()` serves realistic fixtures with zero network, `Paymongo::assertSent()` / `assertNothingSent()` verify traffic, and `Luigel\Paymongo\Testing\Fixtures` builds any resource payload (including webhook events via `Fixtures::event()`). Plain `Http::fake()` works too. See [Testing](https://paymongo.rigelkentcarbonel.com/docs/testing).
