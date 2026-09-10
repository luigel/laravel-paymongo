<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

uses(TestCase::class)->in('E2E');

/*
|--------------------------------------------------------------------------
| Gates
|--------------------------------------------------------------------------
|
| E2E tests talk to the real PayMongo sandbox. They only run when explicitly
| switched on AND when the configured key is unmistakably a test key.
|
*/

/**
 * Why the whole E2E suite should be skipped, or null when it may run.
 */
function e2eSkipReason(): ?string
{
    if (!filter_var(env('PAYMONGO_E2E', false), FILTER_VALIDATE_BOOLEAN)) {
        return 'PAYMONGO_E2E is not set; run the suite through the e2e Docker harness.';
    }

    $secretKey = config('paymongo.secret_key');

    if (!is_string($secretKey) || !str_starts_with($secretKey, 'sk_test_')) {
        return 'PAYMONGO_SECRET_KEY must be a sandbox key (sk_test_...); refusing to run E2E tests.';
    }

    return null;
}

/**
 * Why a test that needs the public tunnel + a registered webhook endpoint
 * should be skipped, or null when it may run.
 */
function e2eWebhookSkipReason(): ?string
{
    $secret = config('paymongo.webhooks.secret');

    if (!is_string($secret) || $secret === '') {
        return 'PAYMONGO_WEBHOOK_SECRET is empty; re-run setup without --no-tunnel to exercise the webhook round trip.';
    }

    return null;
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Clear the delivery log. Never use RefreshDatabase here: the `php artisan
 * serve` process that receives the webhooks shares this SQLite file.
 */
function truncateWebhookDeliveries(): void
{
    DB::table('webhook_deliveries')->delete();
}

/**
 * Poll the shared `webhook_deliveries` table until a row satisfies $where.
 *
 * Rows are written by the long-running HTTP server in another process, so the
 * only way to see them is to re-query.
 *
 * @param callable(object): bool $where
 */
function waitForDelivery(callable $where, int $timeoutSeconds = 45): ?object
{
    $deadline = microtime(true) + $timeoutSeconds;

    while (true) {
        foreach (DB::table('webhook_deliveries')->orderBy('id')->get() as $row) {
            if ($where($row) === true) {
                return $row;
            }
        }

        if (microtime(true) >= $deadline) {
            return null;
        }

        sleep(1);
    }
}

/**
 * Build the `Paymongo-Signature` header for a raw JSON body.
 *
 * Mirrors Luigel\Paymongo\Webhooks\SignatureVerifier: the header is
 * `t=<unix>,te=<hex>,li=<hex>` and each signature is
 * hash_hmac('sha256', "{$t}.{$rawBody}", $secret) — `te` in test mode,
 * `li` in live mode.
 */
function signWebhookPayload(string $json, string $secret, bool $livemode = false, ?int $timestamp = null): string
{
    $timestamp ??= time();

    $signature = hash_hmac('sha256', $timestamp.'.'.$json, $secret);

    return $livemode
        ? "t={$timestamp},te=,li={$signature}"
        : "t={$timestamp},te={$signature},li=";
}

/**
 * POST a raw body to the package webhook route, in-process.
 *
 * The signature covers the exact bytes of the body, so the request must not be
 * re-encoded — hence call() with `content:` rather than postJson().
 *
 * @param array<string, string> $extraServer
 */
function postWebhook(string $body, ?string $signature, array $extraServer = []): TestResponse
{
    $server = array_merge([
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT'  => 'application/json',
    ], $extraServer);

    if ($signature !== null) {
        $server['HTTP_PAYMONGO_SIGNATURE'] = $signature;
    }

    return test()->call('POST', '/paymongo/webhook', server: $server, content: $body);
}
