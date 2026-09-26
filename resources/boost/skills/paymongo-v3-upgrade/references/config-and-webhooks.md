# Config, env, webhooks and artisan commands

## Config file

Source moved from `config/config.php` (v2) to `config/paymongo.php` (v3) and the publish tag changed. Delete the published v2 file and re-publish:

```bash
rm config/paymongo.php
php artisan vendor:publish --tag=paymongo-config
```

Never run `config:cache` as part of the upgrade; clear it instead if it was cached (`php artisan config:clear`).

### Key map

| v2 key | v3 |
|---|---|
| `secret_key` (`PAYMONGO_SECRET_KEY`) | unchanged |
| `public_key` (`PAYMONGO_PUBLIC_KEY`) | unchanged |
| `livemode` (`PAYMONGO_LIVEMODE`) | unchanged |
| `version` (`PAYMONGO_VERSION`) | removed — no API version header is sent |
| `amount_type` | removed — centavos only |
| `signer` | removed — verification is built in |
| `signature_header_name` (`PAYMONGO_SIG_HEADER`) | removed — header is always `Paymongo-Signature` |
| `webhook_signature` (`PAYMONGO_WEBHOOK_SIG`) | `webhooks.secret` (`PAYMONGO_WEBHOOK_SECRET`) — the default endpoint secret |
| `webhook_signatures.{event}` (`PAYMONGO_WEBHOOK_SIG_PAYMENT_PAID`, `..._PAYMENT_FAILED`, `..._SOURCE_CHARGABLE`, `..._PAYMENT_REFUNDED`, `..._PAYMENT_REFUND_UPDATED`) | `webhooks.secrets.{name}` — **per endpoint**, not per event; usually empty |
| — | `base_url` (`PAYMONGO_BASE_URL`, default `https://api.paymongo.com/v1`) |
| — | `http.timeout` (`PAYMONGO_TIMEOUT`, 30), `http.retries` (`PAYMONGO_RETRIES`, 2 retries after the first attempt), `http.retry_delay` (`PAYMONGO_RETRY_DELAY`, 200 ms, doubling), `http.max_retry_delay` (`PAYMONGO_MAX_RETRY_DELAY`, 5000 ms) |
| — | `idempotency.auto` (`PAYMONGO_AUTO_IDEMPOTENCY`, true — auto `Idempotency-Key` on every POST) |
| — | `webhooks.tolerance` (`PAYMONGO_WEBHOOK_TOLERANCE`, 300 s; 0 disables the timestamp check) |
| — | `webhooks.modes.{name}` (optional boolean mode for a named webhook endpoint; defaults to `livemode`) |
| — | `webhooks.dedupe.enabled` (`PAYMONGO_WEBHOOK_DEDUPE`, true), `webhooks.dedupe.ttl` (86400), `webhooks.dedupe.store` (`PAYMONGO_WEBHOOK_DEDUPE_STORE`, null = default cache) |

### v3 config shape

```php
return [
    'secret_key' => env('PAYMONGO_SECRET_KEY'),
    'public_key' => env('PAYMONGO_PUBLIC_KEY'),
    'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
    'livemode' => env('PAYMONGO_LIVEMODE', false),
    'http' => ['timeout' => 30, 'retries' => 2, 'retry_delay' => 200, 'max_retry_delay' => 5000],
    'idempotency' => ['auto' => true],
    'webhooks' => [
        'secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        'secrets' => [], // e.g. ['orders' => env('PAYMONGO_WEBHOOK_SECRET_ORDERS')]
        'modes' => [], // e.g. ['orders' => false] for a test endpoint in a live-mode app
        'tolerance' => 300,
        'dedupe' => ['enabled' => true, 'ttl' => 86400, 'store' => null],
    ],
];
```

### Env rename

```env
# v2 — remove all of these
PAYMONGO_VERSION=2019-08-05
PAYMONGO_SIG_HEADER=paymongo-signature
PAYMONGO_WEBHOOK_SIG=whsk_...
PAYMONGO_WEBHOOK_SIG_PAYMENT_PAID=whsk_...
PAYMONGO_WEBHOOK_SIG_PAYMENT_FAILED=whsk_...

# v3 — one secret per endpoint
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

One PayMongo endpoint has one `secret_key` no matter how many events it subscribes to. If v2 used different values per event, they were almost certainly the same key repeated; when they really differ, PayMongo has several endpoints registered and each one needs its own route + named secret (below).

## Signature middleware

| | v2 | v3 |
|---|---|---|
| Alias | `paymongo.signature` | `paymongo.signature` (unchanged) |
| Class | `Luigel\Paymongo\Middlewares\PaymongoValidateSignature` | `Luigel\Paymongo\Http\Middleware\VerifyWebhookSignature` |
| Parameter | event name -> `config('paymongo.webhook_signatures.{event}')` | secret name -> `config('paymongo.webhooks.secrets.{name}')`; no parameter -> `paymongo.webhooks.secret` |
| Failure | `Illuminate\Routing\Exceptions\InvalidSignatureException` (403) | `abort(401)`; a missing secret throws `RuntimeException` |
| Header | configurable | always `Paymongo-Signature` |
| Timestamp check | none | rejects drift beyond `webhooks.tolerance` |
| Mode | global | `livemode` for the default endpoint, `webhooks.modes.{name}` for a named endpoint when set |

`paymongo.signature:payment_paid` carried over unchanged will fail at runtime because `paymongo.webhooks.secrets.payment_paid` is not configured. Remove the parameter.

## Route macro

```php
use Illuminate\Support\Facades\Route;

Route::paymongoWebhooks();
// POST /paymongo/webhook, middleware paymongo.signature, CSRF exempt, name "paymongo.webhooks"

Route::paymongoWebhooks('webhooks/orders', 'orders');
// custom URI + named secret paymongo.webhooks.secrets.orders (same route name)
```

Signature: `Route::paymongoWebhooks(string $uri = 'paymongo/webhook', ?string $secret = null)`. It works in `routes/web.php` and `routes/api.php` (mind the `/api` prefix when registering the URL with PayMongo).

To keep a custom controller, apply the middleware yourself: `Route::post('my/handler', MyController::class)->middleware('paymongo.signature')`. You then lose events and dedupe; the payload can still be parsed with `Luigel\Paymongo\Webhooks\WebhookEvent::fromArray($request->all())`.

## Events

The built-in controller dispatches `Luigel\Paymongo\Events\WebhookReceived` for every verified event, plus the typed subclass below when the name is known. Unknown future names arrive only as `WebhookReceived` (match on `$event->event->type`).

| Event name | Class (`Luigel\Paymongo\Events\...`) | v2 middleware param it replaces |
|---|---|---|
| `payment.paid` | `PaymentPaid` | `payment_paid` |
| `payment.failed` | `PaymentFailed` | `payment_failed` |
| `source.chargeable` | `SourceChargeable` | `source_chargeable` |
| `payment.refunded` | `PaymentRefunded` | `payment_refunded` |
| `payment.refund.updated` | `PaymentRefundUpdated` | `payment_refund_updated` |
| `payment_intent.succeeded` | `PaymentIntentSucceeded` | — |
| `payment_intent.awaiting_payment_method` | `PaymentIntentAwaitingPaymentMethod` | — |
| `checkout_session.payment.paid` | `CheckoutSessionPaymentPaid` | — |
| `link.payment.paid` | `LinkPaymentPaid` | — |
| `refund.succeeded` | `RefundSucceeded` | — |
| `qrph.expired` | `QrphExpired` | — |
| `qr.paid` / `qr.expired` | `QrPaid` / `QrExpired` | — |
| `subscription.activated` / `.past_due` / `.unpaid` / `.updated` | `SubscriptionActivated` / `SubscriptionPastDue` / `SubscriptionUnpaid` / `SubscriptionUpdated` | — |
| `subscription.invoice.created` / `.finalized` / `.paid` / `.payment_failed` | `SubscriptionInvoiceCreated` / `SubscriptionInvoiceFinalized` / `SubscriptionInvoicePaid` / `SubscriptionInvoicePaymentFailed` | — |
| `dispute.created` / `dispute.resolved` | `DisputeCreated` / `DisputeResolved` | — |
| `payout.deposited` / `payout.returned` | `PayoutDeposited` / `PayoutReturned` | — |

### `WebhookEvent` (the `$event->event` property on every event)

`Luigel\Paymongo\Webhooks\WebhookEvent` is readonly with:

| Member | Meaning |
|---|---|
| `id` | event id `evt_...` (used as the dedupe key) |
| `type` | dotted name, e.g. `payment.paid` |
| `livemode` | bool |
| `data` | the embedded resource `{id, type, attributes}` as an array |
| `timestamp` | `?CarbonImmutable` |
| `raw` | the full request payload |
| `eventType()` | `?Luigel\Paymongo\Enums\WebhookEventType` |
| `resourceId()` | `data.id`, e.g. `pay_...` |
| `resourceAttribute('metadata.order_id', $default)` | dot-notation read of `data.attributes` |

Typed DTOs can be built from the embedded resource: `Luigel\Paymongo\Data\Payment::fromArray($event->event->data)`.

### Converting a v2 controller

```php
// v2: app/Http/Controllers/PaymongoCallbackController.php
public function paymentPaid(Request $request)
{
    $data = $request->input('data.attributes.data');
    $paymentId = $data['id'];
    $amount = $data['attributes']['amount'];               // int centavos even in v2 webhooks
    $orderId = $data['attributes']['metadata']['order_id'] ?? null;
    ...
    return response()->json(['ok' => true]);
}

// v3: app/Listeners/HandlePaymentPaid.php
use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Events\PaymentPaid;

final class HandlePaymentPaid
{
    public function handle(PaymentPaid $event): void
    {
        $paymentId = $event->event->resourceId();
        $amount = $event->event->resourceAttribute('amount');
        $orderId = $event->event->resourceAttribute('metadata.order_id');
        $payment = Payment::fromArray($event->event->data); // optional typed view
        ...
        // no response to return; the controller answers {"received": true}
    }
}
```

Laravel 11+ auto-discovers listeners in `app/Listeners` by the `handle()` type-hint. Otherwise register with `Event::listen(PaymentPaid::class, HandlePaymentPaid::class)` in a service provider. Queue the listener (`ShouldQueue`) if the work is slow; PayMongo retries undelivered events up to 12 times and dedupe keeps retries idempotent.

### Dedupe

The controller stores `paymongo:webhook:{event_id}` in the cache (`webhooks.dedupe.store`, default store) for `webhooks.dedupe.ttl` seconds after dispatch succeeds. It answers `{"received": true}` without dispatching on a repeat. A concurrent delivery gets `503` while the first one is still being handled, so a failed first attempt can be retried. Delete any app-side "already processed" checks keyed on the event id, or keep them only if they guard against replays older than the TTL.

## Artisan commands

| v2 | v3 |
|---|---|
| `php artisan paymongo:webhook` (interactive prompts) | `php artisan paymongo:webhook:create {url} {--event=*}` — defaults to `payment.paid` + `payment.failed`; prints the `secret_key` to put in `PAYMONGO_WEBHOOK_SECRET` |
| `php artisan paymongo:list-webhooks` | `php artisan paymongo:webhook:list` |
| `php artisan paymongo:toggle-webhook {webhookIds?*} --enable/--disable` | `php artisan paymongo:webhook:toggle {id} --enable` or `--disable` (one id) |

```bash
php artisan paymongo:webhook:create https://example.com/paymongo/webhook --event=payment.paid --event=payment.failed
php artisan paymongo:webhook:list
php artisan paymongo:webhook:toggle hook_... --disable
```

If the webhook URL changed during the upgrade (v2 apps typically had `/paymongo/payment-paid` etc.), register the new single endpoint, copy its secret into `PAYMONGO_WEBHOOK_SECRET`, and disable the old per-event endpoints.
