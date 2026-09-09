---
sidebar_position: 12
slug: /webhooks
id: webhooks
---

# Webhooks

Two halves: **registering endpoints** with PayMongo (outbound API calls), and **receiving events** on those endpoints (signature verification, deduplication, Laravel events).

## Registering endpoints

Methods live on `Paymongo::webhooks()` and return `Luigel\Paymongo\Data\Webhook` DTOs.

### Create

```php
use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhooks()->create('https://example.com/paymongo/webhook', [
    'payment.paid',
    'payment.failed',
]);

$webhook->id;        // "hook_9VrvpRkkYqK6twbhuvcVTtjM"
$webhook->secretKey; // "whsk_..." — save as PAYMONGO_WEBHOOK_SECRET, shown only here
```

Event names may also be passed as `Luigel\Paymongo\Enums\WebhookEventType` cases.

### List, retrieve, update, enable, disable

```php
$all = Paymongo::webhooks()->list(); // list<Webhook>

$webhook = Paymongo::webhooks()->retrieve('hook_9VrvpRkkYqK6twbhuvcVTtjM');

$webhook = Paymongo::webhooks()->update('hook_9VrvpRkkYqK6twbhuvcVTtjM', [
    'url' => 'https://example.com/webhooks/paymongo',
    'events' => ['payment.paid', 'payment.failed', 'payment.refunded'],
]);

$webhook = Paymongo::webhooks()->disable('hook_9VrvpRkkYqK6twbhuvcVTtjM');
$webhook = Paymongo::webhooks()->enable('hook_9VrvpRkkYqK6twbhuvcVTtjM');

$webhook->status; // ?WebhookStatus (Enabled | Disabled)
$webhook->events; // list<string>
```

### Artisan commands

```bash
php artisan paymongo:webhook:create https://example.com/paymongo/webhook --event=payment.paid --event=payment.failed
php artisan paymongo:webhook:list
php artisan paymongo:webhook:toggle hook_9VrvpRkkYqK6twbhuvcVTtjM --enable
php artisan paymongo:webhook:toggle hook_9VrvpRkkYqK6twbhuvcVTtjM --disable
```

`paymongo:webhook:create` subscribes to `payment.paid` and `payment.failed` when no `--event` options are given, and prints the endpoint's `secret_key`.

## Receiving events

### 1. Configure the secret

Put the endpoint's `secret_key` in `.env`:

```env
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

### 2. Register the route

```php
// routes/api.php (or routes/web.php — CSRF is excluded automatically)
use Illuminate\Support\Facades\Route;

Route::paymongoWebhooks();
```

This registers `POST /paymongo/webhook` (route name `paymongo.webhooks`) pointing at the package controller, protected by the signature middleware. Customize the URI: `Route::paymongoWebhooks('webhooks/paymongo')`.

### 3. Listen for events

The controller verifies, deduplicates, and dispatches Laravel events. Typed classes exist for every event name PayMongo sends; everything dispatches the generic `Luigel\Paymongo\Events\WebhookReceived` as well:

```php
namespace App\Listeners;

use Luigel\Paymongo\Events\PaymentPaid;

class FulfillOrder
{
    public function handle(PaymentPaid $event): void
    {
        $webhookEvent = $event->event; // Luigel\Paymongo\Webhooks\WebhookEvent

        $webhookEvent->resourceId();                          // "pay_..."
        $webhookEvent->resourceAttribute('amount');           // centavos
        $webhookEvent->resourceAttribute('metadata.order_id');
        $webhookEvent->type;      // "payment.paid"
        $webhookEvent->livemode;  // bool
        $webhookEvent->timestamp; // ?CarbonImmutable
        $webhookEvent->data;      // the full embedded resource array
        $webhookEvent->raw;       // the untouched request payload
    }
}
```

Laravel auto-discovers listeners with type-hinted `handle()` methods; nothing else to register.

### Event classes

| Event name | Class (`Luigel\Paymongo\Events\...`) |
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
| `qr.paid` | `QrPaid` |
| `qr.expired` | `QrExpired` |
| `subscription.activated` | `SubscriptionActivated` |
| `subscription.past_due` | `SubscriptionPastDue` |
| `subscription.unpaid` | `SubscriptionUnpaid` |
| `subscription.updated` | `SubscriptionUpdated` |
| `subscription.invoice.created` | `SubscriptionInvoiceCreated` |
| `subscription.invoice.finalized` | `SubscriptionInvoiceFinalized` |
| `subscription.invoice.paid` | `SubscriptionInvoicePaid` |
| `subscription.invoice.payment_failed` | `SubscriptionInvoicePaymentFailed` |
| `dispute.created` | `DisputeCreated` |
| `dispute.resolved` | `DisputeResolved` |
| `payout.deposited` | `PayoutDeposited` |
| `payout.returned` | `PayoutReturned` |

Every typed class extends `WebhookReceived`, so a `WebhookReceived` listener sees all events. All 25 known event names (`Luigel\Paymongo\Enums\WebhookEventType`) have a typed class; names the package does not know yet arrive as `WebhookReceived` only — match on `$event->event->type` or `$event->event->eventType()` (`?WebhookEventType`).

### Signature verification

The `paymongo.signature` middleware (`Luigel\Paymongo\Http\Middleware\VerifyWebhookSignature`) recomputes the HMAC-SHA256 of the raw body against your webhook secret and rejects mismatches with a 401. Timestamps older than `paymongo.webhooks.tolerance` seconds (default 300; `0` disables) are rejected to block replays. Use it on your own routes too:

```php
Route::post('my/custom/handler', MyWebhookController::class)
    ->middleware('paymongo.signature');
```

### Deduplication

PayMongo retries deliveries (up to 12 times) until it gets a 2xx, so the same event can arrive more than once. The controller remembers each event id in your cache — key `paymongo:webhook:{event_id}`, 24 hour TTL — and skips dispatching duplicates. Configure with `PAYMONGO_WEBHOOK_DEDUPE` (on by default), `PAYMONGO_WEBHOOK_DEDUPE_STORE` (a cache store name; your default store otherwise), and `paymongo.webhooks.dedupe.ttl`.

:::caution
Use a shared cache store (redis, memcached, database) in multi-server deployments so all servers see the same event ids.
:::

### Multiple endpoints

Each PayMongo endpoint has its own secret. Name the extra secrets in `config/paymongo.php`:

```php
'webhooks' => [
    'secret' => env('PAYMONGO_WEBHOOK_SECRET'),
    'secrets' => [
        'orders' => env('PAYMONGO_WEBHOOK_SECRET_ORDERS'),
    ],
],
```

Then pass the name as the macro's second argument (or as a middleware parameter):

```php
Route::paymongoWebhooks();                            // verifies with webhooks.secret
Route::paymongoWebhooks('webhooks/orders', 'orders'); // verifies with webhooks.secrets.orders

Route::post('custom', MyController::class)->middleware('paymongo.signature:orders');
```

### Local development

Expose your local app with a tunnel (e.g. `ngrok http 8000`), register a test-mode webhook against the tunnel URL with `paymongo:webhook:create`, and put the printed `secret_key` in `.env`. For feature tests, build event payloads with `Fixtures::event()` — see [Testing](./testing.md).
