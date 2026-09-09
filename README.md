# Paymongo for Laravel

![Run tests](https://github.com/luigel/laravel-paymongo/workflows/Run%20tests/badge.svg)
[![Quality Score](https://img.shields.io/scrutinizer/g/luigel/laravel-paymongo.svg?style=flat-square)](https://scrutinizer-ci.com/g/luigel/laravel-paymongo)
[![Latest Stable Version](https://poser.pugx.org/luigel/laravel-paymongo/v)](//packagist.org/packages/luigel/laravel-paymongo)
[![Total Downloads](https://poser.pugx.org/luigel/laravel-paymongo/downloads)](//packagist.org/packages/luigel/laravel-paymongo)
[![Monthly Downloads](https://poser.pugx.org/luigel/laravel-paymongo/d/monthly)](//packagist.org/packages/luigel/laravel-paymongo)
[![Daily Downloads](https://poser.pugx.org/luigel/laravel-paymongo/d/daily)](//packagist.org/packages/luigel/laravel-paymongo)
[![License](https://poser.pugx.org/luigel/laravel-paymongo/license)](//packagist.org/packages/luigel/laravel-paymongo)

A Laravel client for the [PayMongo](https://paymongo.com) API. Typed responses, first-class webhooks, built-in testing fakes.

This package is not affiliated with PayMongo.

- Full documentation: https://paymongo.rigelkentcarbonel.com
- Upgrading from 2.x? Read the [upgrade guide](UPGRADE.md).

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quickstart](#quickstart)
- [Amounts are centavos](#amounts-are-centavos)
- [Usage](#usage)
- [Pagination](#pagination)
- [Error handling](#error-handling)
- [Idempotency and retries](#idempotency-and-retries)
- [Webhooks](#webhooks)
- [Multiple accounts](#multiple-accounts)
- [Testing your integration](#testing-your-integration)
- [Version support](#version-support)

## Requirements

- PHP 8.2 or newer (Laravel 13 itself requires PHP 8.3+)
- Laravel 11, 12, or 13

## Installation

```bash
composer require luigel/laravel-paymongo
```

Add your keys to `.env` (grab them from the [PayMongo dashboard](https://dashboard.paymongo.com/developers)):

```env
PAYMONGO_SECRET_KEY=sk_test_...
PAYMONGO_PUBLIC_KEY=pk_test_...
# The endpoint secret_key returned when you register a webhook.
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

Optionally publish the config file to `config/paymongo.php`:

```bash
php artisan vendor:publish --tag=paymongo-config
```

## Quickstart

### Card payment (payment intent lifecycle)

```php
use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Facades\Paymongo;

// 1. Create the intent. Amounts are integer centavos: 150050 = PHP 1,500.50.
$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card'],
    'description' => 'Order #1234',
]);

// 2. Create a payment method (normally done client-side with your public key).
$method = Paymongo::paymentMethods()->create([
    'type' => 'card',
    'details' => [
        'card_number' => '4343434343434345',
        'exp_month' => 12,
        'exp_year' => 34,
        'cvc' => '123',
    ],
]);

// 3. Attach it to the intent to trigger the payment.
$intent = Paymongo::paymentIntents()->attach($intent->id, $method->id);

if ($intent->status === PaymentIntentStatus::Succeeded) {
    // Paid. $intent->payments holds the resulting Payment resources.
}
```

### E-wallet payment (GCash, Maya, GrabPay, ...) with redirect

```php
use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['gcash'],
]);

$method = Paymongo::paymentMethods()->create(['type' => 'gcash']);

// E-wallets require a return_url — where the customer lands after authorizing.
$intent = Paymongo::paymentIntents()->attach(
    $intent->id,
    $method->id,
    returnUrl: route('checkout.complete'),
);

if ($intent->status === PaymentIntentStatus::AwaitingNextAction) {
    // Send the customer to the e-wallet authorization page.
    return redirect()->away($intent->nextAction->url);
}
```

Back on your `return_url`, re-fetch the intent and check its status. The reliable source of truth is the `payment.paid` webhook — see [Webhooks](#webhooks).

## Amounts are centavos

Every amount in and out of this package is an **integer number of centavos** — `150050`, never `1500.50`. This matches the PayMongo API exactly and avoids float rounding bugs. v2 converted floats for you; v3 does not (see [UPGRADE.md](UPGRADE.md)).

For display, amount-bearing resources expose a `money()` helper returning `Luigel\Paymongo\Support\Money`:

```php
use Luigel\Paymongo\Support\Money;

$intent->money()->format();     // "₱1,500.50"
$intent->money()->toDecimal();  // "1500.50" (exact string math, no floats)
$intent->money()->centavos();   // 150050

$total = Money::ofCentavos(100000)->add(Money::ofCentavos(50050));
$total->equals(Money::ofCentavos(150050)); // true
json_encode($total);                       // 150050
```

`Money` also supports `subtract()`, casts to its `format()` string, and serializes to integer centavos in JSON.

## Usage

Every resource is reached through a service on the `Paymongo` facade: `Paymongo::paymentIntents()`, `paymentMethods()`, `payments()`, `refunds()`, `webhooks()`, `checkoutSessions()`, `links()`, `customers()`, `plans()`, `subscriptions()`, and the deprecated `sources()`.

Services take plain attribute arrays (exactly the `data.attributes` from the [PayMongo docs](https://developers.paymongo.com/reference)) and return typed, immutable DTOs from `Luigel\Paymongo\Data`. Enum instances from `Luigel\Paymongo\Enums` may be used anywhere in attribute arrays; they are converted to their string values automatically.

Every DTO exposes:

- typed readonly properties (`$intent->status`, `$payment->billing?->name`, ...), with enum-typed properties (`PaymentIntentStatus`, `PaymentStatus`, ...) that are `null` for values the package does not know yet;
- the raw payload via `$resource->attributes`, `$resource->attribute('dot.notation.key')`, and `$resource->toArray()`;
- `createdAt()` / `updatedAt()` as `CarbonImmutable`.

### Payment intents

```php
use Luigel\Paymongo\Enums\CaptureType;
use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card', 'gcash'],
    'capture_type' => CaptureType::Manual, // authorize now, capture later
]);

$intent = Paymongo::paymentIntents()->retrieve('pi_hsJNpsRFU1LxgVbxW4YJHRs6');

// Client-side retrieval: authenticates with your public key + the intent's client_key.
$intent = Paymongo::paymentIntents()->retrieveUsingClientKey('pi_hsJNpsRFU1LxgVbxW4YJHRs6', $clientKey);

$intent = Paymongo::paymentIntents()->attach('pi_hsJNpsRFU1LxgVbxW4YJHRs6', 'pm_wr98R2gwWroVxfkcNVZBuXg2');

$intent = Paymongo::paymentIntents()->capture('pi_hsJNpsRFU1LxgVbxW4YJHRs6', 100000); // omit amount for full capture

$intent = Paymongo::paymentIntents()->cancel('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
```

### Payment methods

```php
$method = Paymongo::paymentMethods()->create([
    'type' => 'card',
    'details' => ['card_number' => '4343434343434345', 'exp_month' => 12, 'exp_year' => 34, 'cvc' => '123'],
    'billing' => ['name' => 'Juan dela Cruz', 'email' => 'juan@example.com'],
]);

$method = Paymongo::paymentMethods()->retrieve('pm_wr98R2gwWroVxfkcNVZBuXg2');
```

Supported types (`Luigel\Paymongo\Enums\PaymentMethodType`): `card`, `gcash`, `grab_pay`, `paymaya`, `shopee_pay`, `qrph`, `billease`, `dob`, `brankas`, `atome`.

### Payments

Payments are created by PayMongo when an intent succeeds; you read them:

```php
$payment = Paymongo::payments()->retrieve('pay_i35wBzLNdX8i9nKEPaSKWGib');

$page = Paymongo::payments()->list(['limit' => 25]); // CursorPage<Payment>
```

### Refunds

```php
use Luigel\Paymongo\Enums\RefundReason;

$refund = Paymongo::refunds()->create([
    'amount' => 50050,
    'payment_id' => 'pay_i35wBzLNdX8i9nKEPaSKWGib',
    'reason' => RefundReason::Duplicate, // duplicate | fraudulent | others
]);

$refund = Paymongo::refunds()->retrieve('ref_rBCmgwgMXZ9VH4YS2eRooPVL');

$page = Paymongo::refunds()->list(['payment_id' => 'pay_i35wBzLNdX8i9nKEPaSKWGib']);
```

### Checkout sessions

```php
$session = Paymongo::checkoutSessions()->create([
    'line_items' => [
        ['name' => 'T-shirt', 'amount' => 50000, 'currency' => 'PHP', 'quantity' => 3],
    ],
    'payment_method_types' => ['card', 'gcash'],
    'success_url' => 'https://example.com/success',
    'cancel_url' => 'https://example.com/cancel',
]);

return redirect()->away($session->checkoutUrl);
```

```php
$session = Paymongo::checkoutSessions()->retrieve('cs_CbFCTDfxvMFNjwjVi26Uzhtj');
$session = Paymongo::checkoutSessions()->expire('cs_CbFCTDfxvMFNjwjVi26Uzhtj');
```

### Links

```php
$link = Paymongo::links()->create([
    'amount' => 150050,
    'description' => 'Invoice #1234',
    'remarks' => 'laravel-paymongo',
]);

$link = Paymongo::links()->retrieve('link_wWaibr22CzEnficNhQNPUdoo');
$link = Paymongo::links()->retrieveByReference('WTmSJbV'); // null when not found

$page = Paymongo::links()->list(['limit' => 10]);

$link = Paymongo::links()->archive('link_wWaibr22CzEnficNhQNPUdoo');
$link = Paymongo::links()->unarchive('link_wWaibr22CzEnficNhQNPUdoo');
```

### Customers

```php
$customer = Paymongo::customers()->create([
    'first_name' => 'Juan',
    'last_name' => 'dela Cruz',
    'email' => 'juan@example.com',
    'phone' => '+639171234567',
    'default_device' => 'email',
]);

$customer = Paymongo::customers()->retrieve('cus_b9ENKVqcHBfQQmv26uDYDCsD');
$customer = Paymongo::customers()->update('cus_b9ENKVqcHBfQQmv26uDYDCsD', ['first_name' => 'Jane']);
Paymongo::customers()->delete('cus_b9ENKVqcHBfQQmv26uDYDCsD'); // true on success

$saved = Paymongo::customers()->paymentMethods('cus_b9ENKVqcHBfQQmv26uDYDCsD'); // list<CustomerPaymentMethod>
Paymongo::customers()->deletePaymentMethod('cus_b9ENKVqcHBfQQmv26uDYDCsD', 'pm_wr98R2gwWroVxfkcNVZBuXg2');
```

### Plans and subscriptions

```php
use Luigel\Paymongo\Enums\CancellationReason;
use Luigel\Paymongo\Enums\PlanInterval;

$plan = Paymongo::plans()->create([
    'name' => 'Pro',
    'description' => 'Pro tier, billed monthly',
    'amount' => 99900,
    'currency' => 'PHP',
    'interval' => PlanInterval::Monthly,
    'interval_count' => 1,
]);

$plan = Paymongo::plans()->retrieve('plan_9VrvpRkkYqK6twbhuvcVTtjM');
$plan = Paymongo::plans()->update('plan_9VrvpRkkYqK6twbhuvcVTtjM', ['name' => 'Pro (monthly)']);
$page = Paymongo::plans()->list();

$subscription = Paymongo::subscriptions()->create('cus_b9ENKVqcHBfQQmv26uDYDCsD', 'plan_9VrvpRkkYqK6twbhuvcVTtjM');

$subscription = Paymongo::subscriptions()->retrieve('sub_iEbGuGDrxPZoTg9r6BLbdCfV');
$page = Paymongo::subscriptions()->list();

$subscription = Paymongo::subscriptions()->changePlan('sub_iEbGuGDrxPZoTg9r6BLbdCfV', 'plan_hsJNpsRFU1LxgVbxW4YJHRs6');
$subscription = Paymongo::subscriptions()->changePaymentMethod('sub_iEbGuGDrxPZoTg9r6BLbdCfV', 'pm_wr98R2gwWroVxfkcNVZBuXg2');
$subscription = Paymongo::subscriptions()->cancel('sub_iEbGuGDrxPZoTg9r6BLbdCfV', CancellationReason::Unused);

Paymongo::subscriptions()->triggerTestCycle('sub_iEbGuGDrxPZoTg9r6BLbdCfV'); // test mode only
```

### Webhook endpoints (outbound CRUD)

```php
$webhook = Paymongo::webhooks()->create('https://example.com/paymongo/webhook', ['payment.paid', 'payment.failed']);

$webhook->secretKey; // whsk_... — store as PAYMONGO_WEBHOOK_SECRET

$all = Paymongo::webhooks()->list(); // list<Webhook>
$webhook = Paymongo::webhooks()->retrieve('hook_9VrvpRkkYqK6twbhuvcVTtjM');
$webhook = Paymongo::webhooks()->update('hook_9VrvpRkkYqK6twbhuvcVTtjM', ['events' => ['payment.paid']]);
$webhook = Paymongo::webhooks()->enable('hook_9VrvpRkkYqK6twbhuvcVTtjM');
$webhook = Paymongo::webhooks()->disable('hook_9VrvpRkkYqK6twbhuvcVTtjM');
```

### Sources (deprecated)

The PayMongo Sources API is deprecated. Use payment intents with e-wallet payment methods instead. `Paymongo::sources()->create([...])` and `Paymongo::sources()->retrieve('src_...')` remain available for legacy integrations.

## Pagination

List endpoints return a `Luigel\Paymongo\Pagination\CursorPage`, which is iterable and countable:

```php
$page = Paymongo::payments()->list(['limit' => 25]);

foreach ($page as $payment) { /* ... */ }

$page->items;      // list<Payment>
$page->hasMore;    // bool
$page->first();    // ?Payment
$next = $page->nextPage(); // ?CursorPage — fetches with the `after` cursor

// Walk everything lazily across page boundaries:
Paymongo::payments()->list()->lazy()->each(function ($payment) {
    // one HTTP request per page, items streamed one by one
});
```

## Error handling

Any non-2xx API response throws a subclass of `Luigel\Paymongo\Exceptions\PaymongoException`:

| Exception | HTTP status | Meaning |
|---|---|---|
| `AuthenticationException` | 401 | Invalid or missing API key (also thrown when `retrieveUsingClientKey()` is used with no public key configured) |
| `PaymentDeclinedException` | 402 | The processor declined the payment |
| `ResourceNotFoundException` | 404 | The resource does not exist |
| `RateLimitException` | 429 | Rate limited — exposes `$retryAfter` (seconds) from the `Retry-After` header |
| `ServerException` | 500+ | PayMongo-side failure |
| `InvalidRequestException` | 400, 403, 422, other 4xx | Validation and other client errors |
| `ConnectionException` | — | PayMongo could not be reached (DNS, timeout, ...) |
| `InvalidWebhookSignatureException` | — | An inbound webhook failed signature verification |

Every exception carries the parsed PayMongo error payload:

```php
use Luigel\Paymongo\Exceptions\InvalidRequestException;

try {
    Paymongo::paymentIntents()->create(['amount' => 50]); // below the 100-centavo minimum
} catch (InvalidRequestException $e) {
    $e->status;                    // 400
    $e->firstError()?->code;       // "parameter_below_minimum"
    $e->firstError()?->detail;     // human-readable message
    $e->firstError()?->pointer;    // "/data/attributes/amount"
    $e->errors();                  // list<Luigel\Paymongo\Data\ApiError>
}
```

## Idempotency and retries

- **Idempotency**: every POST automatically sends a UUID `Idempotency-Key` header (PayMongo retains keys for 24 hours), so a create that times out can be retried without double-charging. Pass your own key where it matters: `Paymongo::paymentIntents()->create($attributes, idempotencyKey: $orderUuid)` (also on `refunds()->create()` and `checkoutSessions()->create()`). Disable auto-generation with `PAYMONGO_AUTO_IDEMPOTENCY=false`.
- **Retries**: idempotent requests (GET, DELETE, and POSTs carrying an idempotency key) are retried on connection failures and 429/5xx responses — up to `paymongo.http.retries` attempts (default 2), waiting `paymongo.http.retry_delay` ms (default 200) between attempts. PUT/PATCH are never retried.

## Webhooks

### Receiving webhooks

Register the built-in endpoint in `routes/api.php` (or `routes/web.php`):

```php
use Illuminate\Support\Facades\Route;

Route::paymongoWebhooks(); // POST /paymongo/webhook, named "paymongo.webhooks"
```

The macro wires up a controller behind the `paymongo.signature` middleware (CSRF is excluded automatically). It verifies the `Paymongo-Signature` header against `PAYMONGO_WEBHOOK_SECRET` — rejecting bad signatures with a 401 and stale timestamps outside `paymongo.webhooks.tolerance` (default 300 seconds) — then dispatches Laravel events you listen to:

```php
use Luigel\Paymongo\Events\PaymentPaid;

class FulfillOrder
{
    public function handle(PaymentPaid $event): void
    {
        $event->event->resourceId();                     // "pay_..."
        $event->event->resourceAttribute('amount');      // centavos
        $event->event->resourceAttribute('metadata.order_id');
    }
}
```

Every event class wraps a `Luigel\Paymongo\Webhooks\WebhookEvent` with `id`, `type`, `livemode`, `data` (the embedded resource array), `timestamp`, `raw`, plus helpers `eventType()`, `resourceId()`, and `resourceAttribute()`.

A generic `Luigel\Paymongo\Events\WebhookReceived` is dispatched for **every** verified event. These event names additionally dispatch a typed subclass of it:

| Event name | Event class (`Luigel\Paymongo\Events\...`) |
|---|---|
| `payment.paid` | `PaymentPaid` |
| `payment.failed` | `PaymentFailed` |
| `payment.refunded` | `PaymentRefunded` |
| `payment.refund.updated` | `PaymentRefundUpdated` |
| `payment_intent.succeeded` | `PaymentIntentSucceeded` |
| `payment_intent.awaiting_payment_method` | `PaymentIntentAwaitingPaymentMethod` |
| `checkout_session.payment.paid` | `CheckoutSessionPaymentPaid` |
| `link.payment.paid` | `LinkPaymentPaid` |
| `source.chargeable` | `SourceChargeable` |
| `refund.succeeded` | `RefundSucceeded` |
| `qrph.expired` | `QrphExpired` |
| `subscription.activated` | `SubscriptionActivated` |
| `subscription.past_due` | `SubscriptionPastDue` |
| `subscription.unpaid` | `SubscriptionUnpaid` |
| `subscription.updated` | `SubscriptionUpdated` |
| `subscription.invoice.paid` | `SubscriptionInvoicePaid` |
| `subscription.invoice.payment_failed` | `SubscriptionInvoicePaymentFailed` |

All other event names (`subscription.invoice.created`, `subscription.invoice.finalized`, `qr.paid`, `qr.expired`, `dispute.created`, `dispute.resolved`, `payout.deposited`, `payout.returned`, and any future ones) still arrive as `WebhookReceived` — match on `$event->event->type`.

**Deduplication**: PayMongo retries deliveries up to 12 times, so the controller remembers each event id in your cache (key `paymongo:webhook:{event_id}`, 24h TTL) and dispatches events only once. Tune or disable via `paymongo.webhooks.dedupe.*` (`PAYMONGO_WEBHOOK_DEDUPE`, `PAYMONGO_WEBHOOK_DEDUPE_STORE`).

**Custom routes and multiple endpoints**: use the middleware alias directly, optionally naming a secret from `paymongo.webhooks.secrets`:

```php
Route::paymongoWebhooks('webhooks/orders', 'orders'); // verifies with paymongo.webhooks.secrets.orders

Route::post('my/custom/handler', MyController::class)
    ->middleware('paymongo.signature'); // roll your own handling, still signature-verified
```

### Registering endpoints with PayMongo

Through the API (`Paymongo::webhooks()->create(...)`, above) or artisan:

```bash
php artisan paymongo:webhook:create https://example.com/paymongo/webhook --event=payment.paid --event=payment.failed
php artisan paymongo:webhook:list
php artisan paymongo:webhook:toggle hook_9VrvpRkkYqK6twbhuvcVTtjM --disable
php artisan paymongo:webhook:toggle hook_9VrvpRkkYqK6twbhuvcVTtjM --enable
```

`paymongo:webhook:create` prints the endpoint's `secret_key` — put it in `PAYMONGO_WEBHOOK_SECRET`. When no `--event` is given it subscribes to `payment.paid` and `payment.failed`.

## Multiple accounts

`Paymongo::withSecretKey()` returns a manager clone using another key — the global facade stays untouched:

```php
$merchant = Paymongo::withSecretKey($tenant->paymongo_secret_key);

$intent = $merchant->paymentIntents()->create([
    'amount' => 150050,
    'currency' => 'PHP',
    'payment_method_allowed' => ['card'],
]);
```

## Testing your integration

The package never hits the network in your tests. Call `Paymongo::fake()` and every API call is served realistic fixture responses; creates echo your attributes back:

```php
use Luigel\Paymongo\Facades\Paymongo;

public function test_it_charges_the_order(): void
{
    Paymongo::fake();

    $this->post('/checkout', [...])->assertRedirect();

    Paymongo::assertSent(fn ($request) => $request->url() === 'https://api.paymongo.com/v1/payment_intents'
        && $request['data']['attributes']['amount'] === 150050);
}

public function test_nothing_is_charged_for_free_orders(): void
{
    Paymongo::fake();

    $this->post('/checkout', [...]);

    Paymongo::assertNothingSent();
}
```

Stub specific endpoints with `Http::fake()`-style patterns — your stubs win over the built-in catch-all — and build payloads with the same fixture factories the package uses (`Luigel\Paymongo\Testing\Fixtures`, one static factory per resource):

```php
use Luigel\Paymongo\Testing\Fixtures;

Paymongo::fake([
    '*/payment_intents/pi_failing*' => Http::response(['errors' => [['code' => 'resource_failed_state']]], 400),
    '*/payments' => Fixtures::list([Fixtures::payment(['amount' => 150050])], hasMore: false),
]);
```

`Fixtures::event('payment.paid', Fixtures::payment())` builds full webhook event payloads for testing your listeners. Since `Paymongo::fake()` registers plain `Http::fake()` handlers under the hood, `Http::assertSent()`, `Http::fakeSequence()`, and everything else from Laravel's HTTP client testing toolkit work alongside it.

The package's own test suite is fully faked. An opt-in contract suite exercises the real test-mode API — set `PAYMONGO_CONTRACT_TESTS=1` and a `sk_test_...` key to run it.

## Version support

| Package | Laravel | PHP | Status |
|:--------|:--------|:----|:-------|
| 3.x | 11.x – 13.x | 8.2+ (8.3+ for Laravel 13) | Active |
| 2.x | 8.x – 13.x | 8.0+ | Maintenance only |
| 1.x | 5.8 – 8.x | 7.2+ | End of life |

Upgrading from 2.x is a rewrite of your integration points — the [upgrade guide](UPGRADE.md) maps every v2 call to its v3 equivalent.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email rigel20.kent@gmail.com instead of using the issue tracker.

## Credits

-   [Rigel Kent Carbonel](https://github.com/luigel)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
