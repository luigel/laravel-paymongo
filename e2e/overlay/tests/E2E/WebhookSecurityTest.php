<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Luigel\Paymongo\Testing\Fixtures;

/**
 * Signature verification, dedupe and CSRF exclusion, exercised in-process
 * against the real routes/middleware/controller. No tunnel required: the
 * signing secret is overridden at runtime, which is enough because
 * $this->call() runs through this same process's HTTP kernel.
 */
beforeEach(function () {
    if ($reason = e2eSkipReason()) {
        $this->markTestSkipped($reason);
    }

    config([
        'paymongo.webhooks.secret'         => 'whsk_test_e2e',
        'paymongo.webhooks.tolerance'      => 300,
        'paymongo.webhooks.dedupe.enabled' => true,
        'paymongo.livemode'                => false,
    ]);

    truncateWebhookDeliveries();
});

/**
 * A fresh event envelope. The event id must be unique per run: dedupe lives in
 * the shared database cache with a 24h TTL, so reusing the fixture's constant
 * id would make the second run of this suite silently drop deliveries.
 */
$payload = function (string $type = 'payment.paid'): array {
    return Fixtures::event($type, Fixtures::payment(), [
        'id' => 'evt_e2e'.Str::random(24),
    ]);
};

it('rejects a delivery with no signature header', function () use ($payload) {
    $body = json_encode($payload(), JSON_UNESCAPED_SLASHES);

    postWebhook($body, null)->assertStatus(401);

    expect(DB::table('webhook_deliveries')->count())->toBe(0);
});

it('rejects a delivery signed with the wrong secret', function () use ($payload) {
    $body = json_encode($payload(), JSON_UNESCAPED_SLASHES);

    postWebhook($body, signWebhookPayload($body, 'whsk_test_wrong'))->assertStatus(401);

    expect(DB::table('webhook_deliveries')->count())->toBe(0);
});

it('rejects a correctly signed delivery whose timestamp is outside the tolerance', function () use ($payload) {
    $body = json_encode($payload(), JSON_UNESCAPED_SLASHES);

    $stale = signWebhookPayload($body, 'whsk_test_e2e', timestamp: time() - 3600);

    postWebhook($body, $stale)->assertStatus(401);

    expect(DB::table('webhook_deliveries')->count())->toBe(0);
});

it('accepts a correctly signed delivery and records it', function () use ($payload) {
    $event = $payload();
    $body = json_encode($event, JSON_UNESCAPED_SLASHES);

    postWebhook($body, signWebhookPayload($body, 'whsk_test_e2e'))
        ->assertOk()
        ->assertExactJson(['received' => true]);

    $rows = DB::table('webhook_deliveries')->get();

    expect($rows)->toHaveCount(1);
    expect($rows[0]->event_id)->toBe($event['data']['id'])
        ->and($rows[0]->event_type)->toBe('payment.paid')
        ->and($rows[0]->resource_type)->toBe('payment')
        ->and($rows[0]->resource_id)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi');
});

it('deduplicates a replayed delivery of the same event id', function () use ($payload) {
    $event = $payload();
    $body = json_encode($event, JSON_UNESCAPED_SLASHES);
    $signature = signWebhookPayload($body, 'whsk_test_e2e');

    postWebhook($body, $signature)->assertOk()->assertExactJson(['received' => true]);

    // Byte-identical replay, exactly as PayMongo retries.
    postWebhook($body, $signature)->assertOk()->assertExactJson(['received' => true]);

    expect(DB::table('webhook_deliveries')->where('event_id', $event['data']['id'])->count())->toBe(1)
        ->and(DB::table('webhook_deliveries')->count())->toBe(1);
});

it('accepts a delivery carrying no CSRF token', function () use ($payload) {
    $event = $payload();
    $body = json_encode($event, JSON_UNESCAPED_SLASHES);

    // No X-CSRF-TOKEN, no session cookie, no _token field.
    postWebhook($body, signWebhookPayload($body, 'whsk_test_e2e'), [
        'HTTP_ACCEPT' => 'application/json',
    ])->assertOk()->assertExactJson(['received' => true]);

    expect(DB::table('webhook_deliveries')->where('event_id', $event['data']['id'])->count())->toBe(1);
});

/**
 * The CSRF exclusion, over REAL HTTP.
 *
 * Every other test in this file goes through $this->call(), which runs the
 * kernel inside PHPUnit — and the framework's CSRF middleware short-circuits
 * when runningUnitTests() is true, so an in-process test can never see a 419.
 * Only a request to the `php artisan serve` process exercises it for real.
 *
 * That gap hid a real bug: the Router macro excluded ValidateCsrfToken, but
 * Laravel 13 renamed the middleware to PreventRequestForgery and registers
 * *that* in the `web` group, so nothing was excluded and every live delivery
 * got 419 CSRF token mismatch while the whole suite stayed green.
 *
 * The 401 is asserted against `e2e/webhook-csrf-probe` (routes/web.php), a
 * sibling macro route bound to a named secret that is always configured.
 * `/paymongo/webhook` itself cannot answer 401 in a `--no-tunnel` harness,
 * where PAYMONGO_WEBHOOK_SECRET is empty and the signature middleware raises a
 * 500 before rejecting anything — but it must still never answer 419.
 */
it('answers a real unsigned HTTP request from the signature middleware, not from CSRF', function () {
    $base = rtrim((string) env('E2E_SERVER_URL', 'http://127.0.0.1:8000'), '/');

    try {
        Http::timeout(3)->get($base);
    } catch (ConnectionException $e) {
        $this->markTestSkipped(
            "The e2e HTTP server is not reachable at {$base} ({$e->getMessage()}). "
            .'Start it with e2e/bin/setup, or point E2E_SERVER_URL at it.'
        );
    }

    // No Paymongo-Signature, no CSRF token, no session cookie.
    $post = fn (string $path) => Http::timeout(10)
        ->withHeaders(['Accept' => 'application/json'])
        ->withBody('{"data":{"id":"evt_e2e_csrf_probe"}}', 'application/json')
        ->post($base.$path);

    $probe = $post('/e2e/webhook-csrf-probe');

    expect($probe->status())->not->toBe(419)  // 419 = CSRF token mismatch
        ->and($probe->status())->toBe(401);

    $endpoint = $post('/paymongo/webhook');

    expect($endpoint->status())->not->toBe(419)
        ->and($endpoint->status())->toBeIn([401, 500]);
});
