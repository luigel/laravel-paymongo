<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Exceptions\RateLimitException;
use Luigel\Paymongo\Exceptions\ResourceNotFoundException;
use Luigel\Paymongo\Exceptions\ServerException;

beforeEach(function () {
    config()->set('paymongo.http.retry_delay', 0);
});

it('retries GET requests on 429 and succeeds', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(429)
        ->push(['data' => ['id' => 'pay_1', 'type' => 'payment', 'attributes' => []]]);

    $response = app('paymongo')->client()->get('/payments/pay_1');

    expect($response->status)->toBe(200)
        ->and($response->data())->toBe(['id' => 'pay_1', 'type' => 'payment', 'attributes' => []]);

    Http::assertSentCount(2);
});

it('retries GET requests on 5xx and succeeds', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(503)
        ->push(['data' => ['id' => 'pay_1']]);

    $response = app('paymongo')->client()->get('/payments/pay_1');

    expect($response->status)->toBe(200);

    Http::assertSentCount(2);
});

it('gives up after the configured attempts and throws the mapped exception', function () {
    config()->set('paymongo.http.retries', 2);

    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(429)
        ->pushStatus(429);

    expect(fn () => app('paymongo')->client()->get('/payments'))
        ->toThrow(RateLimitException::class);

    Http::assertSentCount(2);
});

it('retries POST requests that carry an idempotency key, reusing the same key', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(502)
        ->push(['data' => ['id' => 'pi_1', 'type' => 'payment_intent', 'attributes' => []]]);

    $response = app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]);

    expect($response->data())->toBe(['id' => 'pi_1', 'type' => 'payment_intent', 'attributes' => []]);

    Http::assertSentCount(2);

    $keys = Http::recorded()->map(
        fn (array $pair): ?string => $pair[0]->header('Idempotency-Key')[0] ?? null
    );

    expect($keys->first())->not->toBeNull()
        ->and($keys->unique())->toHaveCount(1);
});

it('does not retry POST requests without an idempotency key', function () {
    config()->set('paymongo.idempotency.auto', false);

    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(502)
        ->push(['data' => ['id' => 'pi_1']]);

    expect(fn () => app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]))
        ->toThrow(ServerException::class);

    Http::assertSentCount(1);
});

it('does not retry PUT or PATCH requests', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(502)
        ->pushStatus(502);

    $client = app('paymongo')->client();

    expect(fn () => $client->put('/webhooks/hook_1', ['url' => 'https://example.com/hook']))
        ->toThrow(ServerException::class);
    expect(fn () => $client->patch('/customers/cus_1', ['first_name' => 'Juan']))
        ->toThrow(ServerException::class);

    Http::assertSentCount(2);
});

it('does not retry non-retryable statuses', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(404)
        ->push(['data' => []]);

    expect(fn () => app('paymongo')->client()->get('/payments/pay_missing'))
        ->toThrow(ResourceNotFoundException::class);

    Http::assertSentCount(1);
});

it('retries connection failures and succeeds', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushFailedConnection()
        ->push(['data' => ['id' => 'pay_1']]);

    $response = app('paymongo')->client()->get('/payments/pay_1');

    expect($response->status)->toBe(200)
        ->and($response->data())->toBe(['id' => 'pay_1']);

    Http::assertSentCount(2);
});

it('sends the idempotency key on every retry attempt', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(429)
        ->push(['data' => ['id' => 'pi_1']]);

    app('paymongo')->client()->post('/payment_intents', ['amount' => 10000], 'order-42');

    Http::assertSentCount(2);

    expect(Http::recorded(
        fn (Request $request): bool => $request->hasHeader('Idempotency-Key', 'order-42')
    ))->toHaveCount(2);
});
