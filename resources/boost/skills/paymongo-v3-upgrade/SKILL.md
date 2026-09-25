---
name: paymongo-v3-upgrade
description: "Use when upgrading a Laravel app from luigel/laravel-paymongo 2.x to 3.x, or when v2-era code must be made to work on v3. Trigger whenever the app calls Paymongo::paymentIntent(), Paymongo::link(), Paymongo::source(), Paymongo::token(), Paymongo::checkout(), Paymongo::customer(), Paymongo::webhook(), Paymongo::payment(), Paymongo::refund() or Paymongo::paymentMethod(); uses ->find( / ->all() on the facade; reads responses with getStatus(), getAmount(), getData() or other get*() magic getters; sends float peso amounts (1500.50); uses the paymongo.signature:payment_paid middleware, PAYMONGO_WEBHOOK_SIG env vars, the amount_type config key, Paymongo::AMOUNT_TYPE_*, BadRequestException / UnauthorizedException / PaymentErrorException / NotFoundException, or the paymongo:webhook, paymongo:list-webhooks, paymongo:toggle-webhook artisan commands. Do not trigger for apps already on 3.x with no v2 patterns, for writing new v3 integrations from scratch, or for other payment packages."
license: MIT
metadata:
  author: luigel
---

# Upgrade laravel-paymongo 2.x to 3.x

v3 is a from-scratch rewrite. The stateful fluent chain (`Paymongo::paymentIntent()->find($id)->attach(...)`) with magic getters is replaced by per-resource services returning typed readonly DTOs, integer-centavo amounts, a `PaymongoException` tree, built-in inbound webhook handling, and `Paymongo::fake()` for tests.

Work through the steps in order. Finish the audit (step 1) and write the checklist before editing anything. Reference tables live in:

- [references/method-map.md](references/method-map.md) — every v2 facade/model call and its v3 equivalent
- [references/config-and-webhooks.md](references/config-and-webhooks.md) — config keys, env renames, middleware, route macro, events, artisan commands
- [references/exceptions-and-responses.md](references/exceptions-and-responses.md) — exception map, DTO properties, enums, raw access

## 1. Audit the app

Grep the application (not `vendor/`) for each pattern and record every hit in a checklist. Do not edit yet.

```bash
# facade modules (v2 singular names)
grep -rnE "Paymongo::(paymentIntent|paymentMethod|payment|refund|webhook|link|customer|checkout|source|token)\(\)" app routes tests
# chained finders / listers and instance methods
grep -rnE "->(find|all)\(" app routes tests | grep -i paymongo
grep -rnE "->(attach|cancel|archive|unarchive|expire|enable|disable|update|delete|paymentMethods)\(" app | grep -iE "intent|link|checkout|webhook|customer"
grep -rnE "(updateCustomer|deleteCustomer|getPaymentMethods|expireCheckout)\(" app
# magic getters and raw data
grep -rnE "->get[A-Z]\w*\(\)" app | grep -iE "paymongo|intent|payment|link|source|checkout|customer|refund"
grep -rn "getData()" app tests
# amounts
grep -rnE "'amount'\s*=>" app | grep -vE "=>\s*\(int\)"
grep -rnE "amount_type|AMOUNT_TYPE_" app config
# exceptions
grep -rnE "Luigel\\\\Paymongo\\\\Exceptions\\\\(BadRequest|Unauthorized|PaymentError|NotFound|MethodNotFound|AmountTypeNotSupported)Exception" app
# webhooks
grep -rn "paymongo.signature" routes app
grep -rnE "PAYMONGO_WEBHOOK_SIG|webhook_signatures?|signature_header_name|signer" .env* config
# artisan commands
grep -rnE "paymongo:(webhook|list-webhooks|toggle-webhook)\b" app routes .github Makefile composer.json 2>/dev/null
# tests mocking the old client
grep -rnlE "GuzzleHttp|MockHandler|Paymongo::shouldReceive|Mockery::mock\(.*Paymongo" tests
```

Also read `config/paymongo.php`, every webhook controller, and every `routes/*.php` file that mentions PayMongo.

## 2. Composer and requirements

v3 needs PHP 8.2+ (8.3+ on Laravel 13) and Laravel 11, 12 or 13. Bump the constraint in `composer.json` and run `composer require luigel/laravel-paymongo:^3.0`. If the app is on Laravel 10, upgrade Laravel first.

## 3. Config and env

Delete the published `config/paymongo.php` (its v2 shape is incompatible) and re-publish:

```bash
php artisan vendor:publish --tag=paymongo-config
```

Then in `.env`, `.env.example`, and CI secrets replace every `PAYMONGO_WEBHOOK_SIG*` variable with one `PAYMONGO_WEBHOOK_SECRET` (the endpoint's `secret_key`). Drop `PAYMONGO_VERSION` and `PAYMONGO_SIG_HEADER`. Removed keys: `version`, `amount_type`, `signer`, `signature_header_name`, `webhook_signature`, `webhook_signatures`. Full key map in `references/config-and-webhooks.md`.

## 4. Amounts: float pesos to integer centavos

v2 multiplied floats by 100 for you; v3 sends amounts untouched and returns integers. Every `amount` you send (`create()` payloads, `line_items[].amount` on checkout sessions, refund amounts, `capture()`) must be an `int` in centavos.

```php
// v2
Paymongo::link()->create(['amount' => 1500.50, 'description' => 'Invoice #1234']);
$link->getAmount(); // 1500.5

// v3
Paymongo::links()->create(['amount' => 150050, 'description' => 'Invoice #1234']);
$link->amount;               // 150050 (int)
$link->money()->format();    // "₱1,500.50"
$link->money()->toDecimal(); // "1500.50"
```

Where the app stores pesos as decimals, convert once at the boundary: `(int) round($pesos * 100)`. Where it already stores centavos, pass them through unchanged. Never convert twice.

## 5. Facade calls

Three mechanical rules, then check `references/method-map.md` for each hit:

1. Module names become plural services: `paymentIntent()` -> `paymentIntents()`, `checkout()` -> `checkoutSessions()`, `link()` -> `links()`, etc.
2. `find($id)` -> `retrieve($id)`; `all()` -> `list($params = [])` which returns a `Luigel\Paymongo\Pagination\CursorPage`, not a Collection (`->items`, `->hasMore`, `->nextPage()`, `->lazy()` to walk every page).
3. Instance methods move to the service and take the id:

```php
// v2
$intent = Paymongo::paymentIntent()->find($id);
$intent = $intent->attach($paymentMethodId, $returnUrl);

// v3
$intent = Paymongo::paymentIntents()->attach($id, $paymentMethodId, returnUrl: $returnUrl);
```

Removed: `Paymongo::token()` (Tokens API is gone; create payment methods instead) and `Paymongo::payment()->create()` (payments are created by attaching intents, checkout sessions or links). `Paymongo::sources()` still exists but is deprecated (step 9). `Paymongo::webhooks()->create()` now takes positional `($url, $events)`. `Paymongo::links()->find($referenceNumber)` becomes `retrieveByReference($referenceNumber)` (returns `?Link`).

## 6. Responses: magic getters to typed properties

DTOs live in `Luigel\Paymongo\Data\*` with camelCase readonly properties; status/currency/reason fields are PHP enums (`Luigel\Paymongo\Enums\*`). Unknown enum values become `null`, with the raw string still at `->attribute('status')`.

```php
// v2
$intent->getStatus() === 'succeeded';
$method->getBillingName();
$intent->getData();
$intent->getCreatedAt(); // unix int

// v3
use Luigel\Paymongo\Enums\PaymentIntentStatus;

$intent->status === PaymentIntentStatus::Succeeded;
$intent->status?->value;        // "succeeded"
$method->billing?->name;        // Billing value object; ->billing?->address?->city
$intent->attributes;            // raw attributes array
$intent->attribute('payment_method_options.card.request_three_d_secure');
$intent->toArray();             // ['id' => ..., 'type' => ..., 'attributes' => [...]]
$intent->createdAt();           // ?CarbonImmutable
```

Property and enum tables: `references/exceptions-and-responses.md`.

## 7. Exceptions

Replace catch blocks and `use` statements (all live in `Luigel\Paymongo\Exceptions`):

| v2 | v3 |
|---|---|
| `BadRequestException` | `InvalidRequestException` (400/403/422/other 4xx) |
| `UnauthorizedException` | `AuthenticationException` (401) |
| `PaymentErrorException` | `PaymentDeclinedException` (402) |
| `NotFoundException` | `ResourceNotFoundException` (404) |
| `MethodNotFoundException`, `AmountTypeNotSupportedException` | removed, delete the catch |

New: `RateLimitException` (429, `->retryAfter`), `ServerException` (5xx), `ConnectionException`, `InvalidWebhookSignatureException`. Everything extends `PaymongoException` with `->status`, `->errors()` (list of `ApiError` with `code`, `detail`, `pointer`, `attribute`) and `->firstError()`. A single `catch (PaymongoException $e)` is usually the right replacement for a v2 catch-all.

## 8. Webhooks

The `paymongo.signature` middleware parameter changed meaning: in v2 it named an event (`payment_paid`) and picked a per-event secret; in v3 it names an entry in `paymongo.webhooks.secrets` (per endpoint). Replace per-event routes and the custom controller with the route macro plus listeners:

```php
// v2 routes/web.php
Route::post('/paymongo/payment-paid', [PaymongoCallbackController::class, 'paymentPaid'])
    ->middleware('paymongo.signature:payment_paid');

// v3 routes/api.php (or web.php) — one route, all events, CSRF-exempt, named "paymongo.webhooks"
Route::paymongoWebhooks();                          // POST /paymongo/webhook, PAYMONGO_WEBHOOK_SECRET
Route::paymongoWebhooks('webhooks/orders', 'orders'); // second endpoint, paymongo.webhooks.secrets.orders
```

Convert each controller action into a listener on the typed event (`Luigel\Paymongo\Events\PaymentPaid`, `PaymentFailed`, `SourceChargeable`, ...; the generic `WebhookReceived` fires for every event):

```php
// v2 controller action
public function paymentPaid(Request $request)
{
    $payment = $request->input('data.attributes.data');
    Order::where('reference', $payment['attributes']['metadata']['order_id'])->markPaid($payment['id']);
}

// v3 listener (register in the event's `handle` type-hint or via Event::listen)
use Luigel\Paymongo\Events\PaymentPaid;

public function handle(PaymentPaid $event): void
{
    $orderId = $event->event->resourceAttribute('metadata.order_id');
    $amount = $event->event->resourceAttribute('amount'); // int centavos
    Order::where('reference', $orderId)->markPaid($event->event->resourceId());
}
```

Duplicate deliveries are dropped automatically via the cache; remove any hand-rolled dedupe. Update the endpoint URL registered with PayMongo if the path changed (`/paymongo/webhook` by default). Event class table and `WebhookEvent` API: `references/config-and-webhooks.md`.

## 9. Sources flow (deprecated)

The v2 source -> redirect -> `source.chargeable` -> `payment()->create()` flow no longer has a create-payment step. Rewrite e-wallet checkouts as payment intents:

```php
$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['gcash'],
]);
$method = Paymongo::paymentMethods()->create(['type' => 'gcash']);
$intent = Paymongo::paymentIntents()->attach($intent->id, $method->id, returnUrl: route('checkout.complete'));

return redirect()->away($intent->nextAction->url); // then listen for PaymentPaid
```

If you must keep sources temporarily, `Paymongo::sources()->create()` / `->retrieve()` still exist (deprecated), but there is no way to create the payment afterwards.

## 10. Artisan commands

`paymongo:webhook` -> `paymongo:webhook:create {url} {--event=*}` (no prompts), `paymongo:list-webhooks` -> `paymongo:webhook:list`, `paymongo:toggle-webhook {id} --enable|--disable` -> `paymongo:webhook:toggle {id} --enable|--disable`. Update scripts, docs and CI.

## 11. Tests

Delete Guzzle `MockHandler` setups and `Paymongo::shouldReceive()` mocks; use the built-in fake:

```php
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Events\PaymentPaid;
use Luigel\Paymongo\Testing\Fixtures;
use Luigel\Paymongo\Webhooks\WebhookEvent;

Paymongo::fake(); // every API call served realistic fixtures, creates echo attributes back
$this->post('/checkout', [...])->assertRedirect();
Paymongo::assertSent(fn ($request) => str_ends_with($request->url(), '/payment_intents')
    && $request['data']['attributes']['amount'] === 150050);
Paymongo::assertNothingSent();

// stub a specific endpoint (stubs win over the catch-all)
Paymongo::fake(['*/payments' => Fixtures::list([Fixtures::payment(['amount' => 150050])])]);

// exercise a webhook listener end to end (header = HMAC-SHA256 of "{timestamp}.{body}" with the secret)
config(['paymongo.webhooks.secret' => 'whsk_test']);
$body = json_encode(Fixtures::event('payment.paid', Fixtures::payment(['amount' => 150050])));
$ts = time();
$this->call('POST', route('paymongo.webhooks'), [], [], [], [
    'CONTENT_TYPE' => 'application/json',
    'HTTP_PAYMONGO_SIGNATURE' => "t={$ts},te=".hash_hmac('sha256', "{$ts}.{$body}", 'whsk_test').',li=',
], $body)->assertOk();

// or skip transport and fire the event directly
event(new PaymentPaid(WebhookEvent::fromArray(Fixtures::event('payment.paid', Fixtures::payment()))));
```

Fixture factories exist for every resource (`Fixtures::paymentIntent()`, `link()`, `checkoutSession()`, `customer()`, `refund()`, `webhook()`, ...). Amount assertions must now expect integers.

## 12. Verify

1. Run the app's tests scoped to the touched areas (through the project's Docker wrapper if it uses one).
2. Re-run every grep from step 1; all must be empty. Also grep for `->status ===? '` string comparisons that should now compare enums, and for `Collection` type-hints on `list()` results.
3. `php artisan route:list --name=paymongo` shows the webhook route with `paymongo.signature` middleware.
4. `php artisan config:show paymongo` (or `config('paymongo.webhooks.secret')` in tinker) resolves the new secret; no `amount_type` / `webhook_signatures` keys remain.
5. Spot-check one create call and one list call against test-mode keys if credentials are available.

## Common pitfalls

- Converting twice: an app that already stored centavos and sent them with `amount_type => int` must not be multiplied by 100 again.
- Apps that passed a reference number to `Paymongo::link()->find()` must switch to `links()->retrieveByReference($referenceNumber)`, which returns `null` (not an exception) when nothing matches; `retrieve()` only accepts a `link_...` id.
- Enum properties are `null` for API states this package does not know; use `->attribute('status')` for the raw string before treating `null` as "missing".
- `paymongo.signature:payment_paid` no longer means "the payment_paid event": it looks up `paymongo.webhooks.secrets.payment_paid` and throws a `RuntimeException` when that key is unset. Drop the parameter unless you configured named secrets.
- `list()` returns a `CursorPage`, not a Collection: `->items` / `->first()` / `->lazy()`, no `->map()` or `->count()` semantics beyond the current page. `customers()->paymentMethods()` returns a plain PHP array.
- `webhooks()->create($url, $events)` takes positional arguments, not the v2 `['url' => ..., 'events' => ...]` array.
- `attach()` signature is `attach(string $id, string $paymentMethodId, ?string $returnUrl = null, ?string $clientKey = null)`; e-wallets require `returnUrl`.
- Idempotency keys are auto-generated on every POST; retries re-use them. Pass your own via `create($attributes, idempotencyKey: ...)` when you have a natural key.
- The route macro name is always `paymongo.webhooks`; registering two macros without renaming one will collide in `route()` lookups.
