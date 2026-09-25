---
title: Webhooks
slug: webhooks
order: 50
section: Integration
operations:
  - webhooks.create
  - webhooks.list
  - webhooks.retrieve
  - webhooks.update
  - webhooks.enable
  - webhooks.disable
---

# Webhooks

PayMongo calls a URL on your app, a webhook endpoint, whenever something happens on your account: a payment is paid, a refund goes through, a subscription renews. Whichever way you take payments, the webhook is how you learn the money arrived. A customer reaching your success page only means they came back.

There are two halves. You **register** an endpoint with PayMongo through `Paymongo::webhooks()`, and you **receive** its deliveries with one route, which verifies them, drops repeats, and dispatches a Laravel event for each. For PayMongo's side, see its [Webhooks guide](https://docs.paymongo.com/docs/developer-tools-webhooks) and [Events](https://docs.paymongo.com/docs/developer-tools-webhooks-events).

## Receive events

### 1. Register the route

```php include=examples/receiving-webhooks/routes.php
```

`Route::paymongoWebhooks()` registers `POST /paymongo/webhook`, named `paymongo.webhooks`, pointing at the package's controller. Pass a different URI as the first argument: `Route::paymongoWebhooks('webhooks/paymongo')`. It turns off CSRF protection for the route itself, so it works from `routes/web.php` as well as `routes/api.php`.

For every delivery, the route:

1. **Verifies the signature**, and answers `401` to anything PayMongo did not sign.
2. **Drops repeat deliveries** of an event it has already handled, answering `200` without dispatching anything.
3. **Dispatches** `Luigel\Paymongo\Events\WebhookReceived` for every event, and the typed event for its name, such as `PaymentPaid` for `payment.paid`.
4. **Answers** `200` with `{"received": true}`.

### 2. Add the endpoint's secret

Each endpoint has its own `secret_key`, which PayMongo uses to sign every delivery to it. Put it in `.env`:

```env
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

PayMongo shows the secret when you create the endpoint, either in the dashboard or from [`create()`](#register-an-endpoint). The route throws a `RuntimeException` on every delivery until it is set.

### 3. Listen for the event

Write a listener for the typed event you care about:

```php include=examples/receiving-webhooks/MarkOrderPaid.php
```

Laravel discovers the listener from the type hint on `handle()`, so there is nothing to register. `$event->event` is a `Luigel\Paymongo\Webhooks\WebhookEvent`:

| Member | What it is |
|:-------|:-----------|
| `id` | The event's id, `evt_...` |
| `type` | Its name, such as `payment.paid` |
| `eventType()` | The name as a `Luigel\Paymongo\Enums\WebhookEventType`, or `null` for a name the package does not know |
| `livemode` | `true` for a live-mode event |
| `timestamp` | When PayMongo created the event, a `?CarbonImmutable` |
| `resourceId()` | The id of the resource it is about, such as `pay_...` |
| `resourceAttribute($key, $default)` | One of that resource's attributes, by dot path |
| `data` | The whole resource, `{id, type, attributes}` |
| `raw` | The payload exactly as PayMongo posted it |

The resource is a snapshot from when the event happened, as a plain array rather than a data object. When you need its current state, retrieve it, for example with `Paymongo::payments()->retrieve($webhookEvent->resourceId())`.

To see every event, including names the package has no class for yet, listen for `WebhookReceived`. Every typed event extends it:

```php include=examples/receiving-webhooks/RecordWebhookEvent.php
```

The [Events reference](./reference/events.md) lists every typed event and the PayMongo event name it is dispatched for. [Choose a flow](./choose-a-flow.md) says which one confirms payment for each way of taking it.

### Write listeners that survive retries

PayMongo expects a `2xx` within 30 seconds. Otherwise it retries the delivery, up to 12 times with exponential backoff, so one event can reach you more than once. So:

- **Queue the work.** Implement `ShouldQueue`, as `MarkOrderPaid` does, so the route answers at once and a slow or failing listener is retried by your queue worker. The package marks an event handled *before* dispatching it, so if a listener that runs inside the request throws, PayMongo's retry is dropped as a repeat and the event is not handled again.
- **Make the listener idempotent.** Deduplication only remembers an event for 24 hours, in your cache. Check your own state, as `MarkOrderPaid` checks `paid_at`, before acting.
- **Check what you were paid.** Compare the amount, and whatever reference you put in `metadata`, with your order before fulfilling it.

### Signature verification

The route runs the `paymongo.signature` middleware (`Luigel\Paymongo\Http\Middleware\VerifyWebhookSignature`). It reads the `Paymongo-Signature` header, `t=<timestamp>,te=<test-mode signature>,li=<live-mode signature>`, and recomputes the HMAC-SHA256 of `"{t}.{raw body}"` with your secret, as PayMongo's [Securing a webhook](https://docs.paymongo.com/docs/developer-tools-webhook-setup-management) describes. It answers `401` when:

- the header is missing, or has no timestamp;
- the signature does not match. It checks `te` unless `PAYMONGO_LIVEMODE=true`, when it checks `li`, so set that in production;
- the timestamp is more than `PAYMONGO_WEBHOOK_TOLERANCE` seconds away from now (`300` by default, `0` turns the check off). This stops an old delivery from being replayed.

Put the middleware on a route of your own when you want to handle deliveries yourself instead of through events:

```php include=examples/receiving-webhooks/custom-route.php
```

### Deduplication

The route remembers each event id in your cache under `paymongo:webhook:{event id}` for 24 hours, and skips an event it has seen. Settings:

| Variable or key | Default | What it does |
|:----------------|:--------|:-------------|
| `PAYMONGO_WEBHOOK_DEDUPE` | `true` | Turn deduplication on or off |
| `PAYMONGO_WEBHOOK_DEDUPE_STORE` | your default cache store | The cache store to remember events in |
| `paymongo.webhooks.dedupe.ttl` | `86400` | How long to remember an event, in seconds |

Running more than one server? Use a cache store they share, such as Redis, Memcached, or the database. With the `file` or `array` store, each server only knows the events it handled itself.

### More than one endpoint

Each endpoint has its own secret, so a second endpoint, for another PayMongo account, say, needs its own. Publish the config file (`php artisan vendor:publish --tag=paymongo-config`) and name the secret under `webhooks.secrets` in `config/paymongo.php`, for example `'orders' => env('PAYMONGO_WEBHOOK_SECRET_ORDERS')`. Then pass that name as the second argument:

```php include=examples/receiving-webhooks/multiple-endpoints.php
```

On a route of your own, name the secret as the middleware's parameter: `paymongo.signature:orders`.

### Test locally

PayMongo needs a public HTTPS URL. Expose your app with a tunnel such as `ngrok http 8000`, register a test-mode endpoint for the tunnel's URL, and put its secret in `.env`. Test-mode endpoints only receive test-mode events. PayMongo's dashboard can also send a [test event](https://docs.paymongo.com/docs/webhook-testing) to an endpoint.

In your test suite, post events built with `Fixtures::event()` to the route instead. See [Testing](./testing.md#test-a-webhook).

## Register an endpoint

Register endpoints from code or with the artisan commands. Every method lives on `Paymongo::webhooks()` and returns a `Luigel\Paymongo\Data\Webhook` (see the [Webhook reference](./reference/data-objects.md#webhook)). An endpoint belongs to the mode of the key that created it, so register one with your test key and another with your live key.

`create()` takes the URL and the events to send to it, as `WebhookEventType` cases or strings:

```php include=examples/webhooks/create.php
```

PayMongo only returns `secretKey` here, so store it straight away.

`list()` returns every endpoint on the account as a plain array, not a page. `retrieve()` returns one:

```php include=examples/webhooks/list-and-retrieve.php
```

`update()` changes an endpoint's `url` or `events`:

```php include=examples/webhooks/update.php
```

`disable()` stops deliveries to an endpoint without removing it, and `enable()` starts them again. PayMongo does not replay the events it skipped in between:

```php include=examples/webhooks/disable-and-enable.php
```

The package cannot delete an endpoint. An endpoint you no longer want stays on the account, disabled.

### From the command line

```bash
php artisan paymongo:webhook:create https://example.com/paymongo/webhook --event=payment.paid --event=payment.failed
php artisan paymongo:webhook:list
php artisan paymongo:webhook:toggle hook_9VrvpRkkYqK6twbhuvcVTtjM --disable
php artisan paymongo:webhook:toggle hook_9VrvpRkkYqK6twbhuvcVTtjM --enable
```

`paymongo:webhook:create` subscribes to `payment.paid` and `payment.failed` when you pass no `--event`, and prints the endpoint's secret. See [Artisan commands](./reference/artisan-commands.md).
