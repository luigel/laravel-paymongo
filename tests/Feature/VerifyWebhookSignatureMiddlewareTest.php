<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;

/**
 * Build a `Paymongo-Signature` header signing $payload at $timestamp.
 */
function middleware_signature_header(string $payload, string $secret, int $timestamp, bool $livemode = false): string
{
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return $livemode
        ? "t={$timestamp},te=,li={$signature}"
        : "t={$timestamp},te={$signature},li=";
}

/**
 * POST a raw JSON body with the given signature header.
 */
function post_signed_webhook(string $uri, string $body, ?string $header): TestResponse
{
    $server = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
    ];

    if ($header !== null) {
        $server['HTTP_PAYMONGO_SIGNATURE'] = $header;
    }

    return test()->call('POST', $uri, server: $server, content: $body);
}

beforeEach(function () {
    Route::post('/test-hook', fn () => response()->json(['ok' => true]))
        ->middleware('paymongo.signature');
});

it('passes a request signed over the exact raw body, even when it differs from any re-encoding', function () {
    // Whitespace and key order that json_encode(json_decode(...)) would not reproduce.
    $body = "{\n  \"zulu\": 1,\n  \"alpha\": {\"nested\": true}\n}";
    $header = middleware_signature_header($body, 'whsk_test_fake', time());

    post_signed_webhook('/test-hook', $body, $header)
        ->assertOk()
        ->assertExactJson(['ok' => true]);
});

it('rejects an invalid signature with a 401', function () {
    $body = '{"data":{"id":"evt_1"}}';
    $header = middleware_signature_header($body, 'whsk_wrong_secret', time());

    post_signed_webhook('/test-hook', $body, $header)->assertUnauthorized();
});

it('rejects a request without a signature header with a 401', function () {
    post_signed_webhook('/test-hook', '{"data":{"id":"evt_1"}}', null)->assertUnauthorized();
});

it('verifies against the li component when livemode is on', function () {
    config()->set('paymongo.livemode', true);

    $body = '{"data":{"id":"evt_1"}}';

    post_signed_webhook('/test-hook', $body, middleware_signature_header($body, 'whsk_test_fake', time(), livemode: true))
        ->assertOk();

    // A test-mode-only header no longer satisfies live mode.
    post_signed_webhook('/test-hook', $body, middleware_signature_header($body, 'whsk_test_fake', time()))
        ->assertUnauthorized();
});

it('looks up a named secret from paymongo.webhooks.secrets', function () {
    config()->set('paymongo.webhooks.secrets.orders', 'whsk_orders_secret');

    Route::post('/orders-hook', fn () => response()->json(['ok' => true]))
        ->middleware('paymongo.signature:orders');

    $body = '{"data":{"id":"evt_1"}}';

    post_signed_webhook('/orders-hook', $body, middleware_signature_header($body, 'whsk_orders_secret', time()))
        ->assertOk();

    // The default secret does not verify a named-secret endpoint.
    post_signed_webhook('/orders-hook', $body, middleware_signature_header($body, 'whsk_test_fake', time()))
        ->assertUnauthorized();
});

it('uses each named endpoint mode for mixed test and live webhooks', function () {
    config()->set('paymongo.livemode', true);
    config()->set('paymongo.webhooks.secrets.orders', 'whsk_orders_secret');
    config()->set('paymongo.webhooks.modes.orders', false);

    Route::post('/orders-hook', fn () => response()->json(['ok' => true]))
        ->middleware('paymongo.signature:orders');

    $body = '{"data":{"id":"evt_1"}}';

    post_signed_webhook('/orders-hook', $body, middleware_signature_header($body, 'whsk_orders_secret', time()))
        ->assertOk();
    post_signed_webhook('/orders-hook', $body, middleware_signature_header($body, 'whsk_orders_secret', time(), livemode: true))
        ->assertUnauthorized();

    post_signed_webhook('/test-hook', $body, middleware_signature_header($body, 'whsk_test_fake', time(), livemode: true))
        ->assertOk();

    config()->set('paymongo.livemode', false);
    config()->set('paymongo.webhooks.modes.orders', true);

    post_signed_webhook('/orders-hook', $body, middleware_signature_header($body, 'whsk_orders_secret', time(), livemode: true))
        ->assertOk();
    post_signed_webhook('/orders-hook', $body, middleware_signature_header($body, 'whsk_orders_secret', time()))
        ->assertUnauthorized();
});

it('fails with a 500 when the webhook secret is not configured', function () {
    config()->set('paymongo.webhooks.secret');

    $body = '{"data":{"id":"evt_1"}}';

    post_signed_webhook('/test-hook', $body, middleware_signature_header($body, 'whsk_test_fake', time()))
        ->assertStatus(500);
});

it('fails with a 500 when a named secret is not configured', function () {
    Route::post('/missing-hook', fn () => response()->json(['ok' => true]))
        ->middleware('paymongo.signature:missing');

    $body = '{"data":{"id":"evt_1"}}';

    post_signed_webhook('/missing-hook', $body, middleware_signature_header($body, 'whsk_test_fake', time()))
        ->assertStatus(500);
});
