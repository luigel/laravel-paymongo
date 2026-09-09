# E2E suite

Runs inside the e2e container against the real PayMongo sandbox:
`docker compose exec -e PAYMONGO_E2E=1 app vendor/bin/pest -c phpunit.e2e.xml tests/E2E`.

- `BootTest.php` — package wiring: provider/facade, `sk_test_` key, the `paymongo.webhooks` route (`paymongo/webhook`, with no `Csrf`/`RequestForgery` middleware left on it while the `web` group still has one), the `paymongo.signature` alias, and `artisan paymongo:webhook:list`. Needs the API, not the tunnel.
- `WebhookSecurityTest.php` — in-process POSTs to `/paymongo/webhook`: missing/bad/stale signatures are `401`, a valid one returns `{"received":true}` and logs one row, a byte-identical replay is deduped, and no CSRF token is required. Overrides the signing secret at runtime, so **no tunnel needed**. Its last test is the only one that leaves the process: a real HTTP POST to the `artisan serve` server, because the framework's CSRF middleware short-circuits under `runningUnitTests()` and an in-process test can never see a `419`. It asserts `401` against `e2e/webhook-csrf-probe` (a sibling macro route bound to a named secret, since `/paymongo/webhook` can only answer `500` without `PAYMONGO_WEBHOOK_SECRET`) and `not 419` against both.
- `CardPaymentWebhookTest.php` — **needs the tunnel**: charges sandbox card `4343434343434345` through a payment intent, waits (60s) for the real `payment.paid` delivery, then refunds it and waits (90s) for `payment.refunded` (incomplete, not failed, when the sandbox is slow).

Every test skips unless `PAYMONGO_E2E` is truthy and `PAYMONGO_SECRET_KEY` starts with `sk_test_`; the card test also skips when `PAYMONGO_WEBHOOK_SECRET` is empty (`--no-tunnel` setup).
Deliveries are observed through the shared `webhook_deliveries` table (written by the `artisan serve` process), truncated in `beforeEach` — never `RefreshDatabase`.
