<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
 * Landing page for `return_url` when a card requires 3DS. The E2E suite uses a
 * non-3DS test card, so nothing normally redirects here — it just keeps the URL
 * we hand to PayMongo from being a 404.
 */
Route::get('/e2e/return', function () {
    return response()->json(['returned' => true]);
})->name('e2e.return');

/*
 * POST /paymongo/webhook, name `paymongo.webhooks`, signature-verified and
 * excluded from CSRF by the package's Router macro.
 */
Route::paymongoWebhooks();

/*
 * A second macro route, used by WebhookSecurityTest to prove over REAL HTTP
 * that a `paymongoWebhooks` endpoint answers 401 from the package's signature
 * middleware and never 419 from the `web` group's CSRF middleware.
 *
 * The endpoint above cannot serve that purpose in a `--no-tunnel` harness:
 * PAYMONGO_WEBHOOK_SECRET is empty there, so the signature middleware raises a
 * 500 before it can reject anything. This one verifies against the named
 * secret registered in App\Providers\AppServiceProvider, which is always set.
 *
 * Route::name() appends, so the macro's `paymongo.webhooks` becomes
 * `paymongo.webhooks.csrf-probe` — a distinct name, leaving the lookup for the
 * real endpoint alone.
 */
Route::paymongoWebhooks('e2e/webhook-csrf-probe', 'csrf_probe')->name('.csrf-probe');
