<?php

declare(strict_types=1);

use Carbon\CarbonInterval;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Luigel\Paymongo\Exceptions\ConnectionException;
use Luigel\Paymongo\Exceptions\RateLimitException;
use Luigel\Paymongo\Exceptions\ResourceNotFoundException;
use Luigel\Paymongo\Exceptions\ServerException;

beforeEach(function () {
    config()->set('paymongo.http.retry_delay', 0);
});

/**
 * A connection failure carrying the cURL error number the way Guzzle's cURL handler reports it.
 */
function failedConnectionWithCurlError(int $errno): Closure
{
    return fn (Request $request): PromiseInterface => Create::rejectionFor(new ConnectException(
        "cURL error {$errno}",
        $request->toPsrRequest(),
        null,
        ['errno' => $errno],
    ));
}

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

it('retries the configured number of times after the first attempt, then throws the mapped exception', function () {
    config()->set('paymongo.http.retries', 2);

    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(429)
        ->pushStatus(429)
        ->pushStatus(429);

    expect(fn () => app('paymongo')->client()->get('/payments'))
        ->toThrow(RateLimitException::class);

    Http::assertSentCount(3);
});

it('does not retry at all when retries is zero', function () {
    config()->set('paymongo.http.retries', 0);

    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(503)
        ->push(['data' => ['id' => 'pay_1']]);

    expect(fn () => app('paymongo')->client()->get('/payments'))
        ->toThrow(ServerException::class);

    Http::assertSentCount(1);
});

it('retries a rate-limited POST, reusing the same idempotency key', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(429)
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

it('retries a rate-limited POST even without an idempotency key', function () {
    config()->set('paymongo.idempotency.auto', false);

    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(429)
        ->push(['data' => ['id' => 'pi_1']]);

    app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]);

    Http::assertSentCount(2);
});

it('retries a POST that carries an idempotency key after a 5xx, reusing the same key', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(502)
        ->push(['data' => ['id' => 'pi_1']]);

    $response = app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]);

    expect($response->data())->toBe(['id' => 'pi_1']);

    Http::assertSentCount(2);

    $keys = Http::recorded()->map(
        fn (array $pair): ?string => $pair[0]->header('Idempotency-Key')[0] ?? null
    );

    expect($keys->first())->not->toBeNull()
        ->and($keys->unique())->toHaveCount(1);
});

it('does not retry a POST without an idempotency key after a 5xx', function () {
    config()->set('paymongo.idempotency.auto', false);

    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(502)
        ->push(['data' => ['id' => 'pi_1']]);

    expect(fn () => app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]))
        ->toThrow(ServerException::class);

    Http::assertSentCount(1);
});

it('retries a POST without an idempotency key whose connection failed before the request was sent', function (Closure $failure) {
    config()->set('paymongo.idempotency.auto', false);

    Http::fakeSequence('api.paymongo.com/*')
        ->pushResponse($failure)
        ->push(['data' => ['id' => 'pi_1']]);

    app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]);

    Http::assertSentCount(2);
})->with([
    'host not resolved, from the message' => fn () => Http::failedConnection(),
    'connection refused, from the handler context' => fn (): Closure => failedConnectionWithCurlError(7),
]);

it('does not retry a POST without an idempotency key that timed out', function () {
    config()->set('paymongo.idempotency.auto', false);

    Http::fakeSequence('api.paymongo.com/*')
        ->pushResponse(failedConnectionWithCurlError(28))
        ->push(['data' => ['id' => 'pi_1']]);

    expect(fn () => app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]))
        ->toThrow(ConnectionException::class);

    Http::assertSentCount(1);
});

it('does not retry PUT or PATCH requests after a 5xx', function () {
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

it('retries rate-limited PUT and PATCH requests', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(429)
        ->push(['data' => ['id' => 'hook_1']])
        ->pushStatus(429)
        ->push(['data' => ['id' => 'cus_1']]);

    $client = app('paymongo')->client();
    $client->put('/webhooks/hook_1', ['url' => 'https://example.com/hook']);
    $client->patch('/customers/cus_1', ['first_name' => 'Juan']);

    Http::assertSentCount(4);
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
        ->pushResponse(failedConnectionWithCurlError(28))
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

it('waits as long as the Retry-After header asks', function () {
    Sleep::fake();

    Http::fakeSequence('api.paymongo.com/*')
        ->push(['errors' => [['code' => 'rate_limited', 'detail' => 'Too many requests.']]], 429, ['Retry-After' => '2'])
        ->push(['data' => ['id' => 'pay_1']]);

    app('paymongo')->client()->get('/payments/pay_1');

    Http::assertSentCount(2);
    Sleep::assertSequence([Sleep::for(2)->seconds()]);
});

it('does not wait for a Retry-After longer than the maximum retry delay', function () {
    Sleep::fake();
    config()->set('paymongo.http.max_retry_delay', 5000);

    Http::fakeSequence('api.paymongo.com/*')
        ->push(['errors' => []], 429, ['Retry-After' => '60'])
        ->push(['data' => ['id' => 'pay_1']]);

    try {
        app('paymongo')->client()->get('/payments/pay_1');
        $this->fail('Expected RateLimitException was not thrown.');
    } catch (RateLimitException $exception) {
        expect($exception->retryAfter)->toBe(60);
    }

    Http::assertSentCount(1);
    Sleep::assertNeverSlept();
});

it('backs off exponentially with jitter, capped at the maximum retry delay', function () {
    Sleep::fake();
    config()->set('paymongo.http.retries', 4);
    config()->set('paymongo.http.retry_delay', 200);
    config()->set('paymongo.http.max_retry_delay', 1000);

    $waits = [];
    Sleep::whenFakingSleep(function (CarbonInterval $duration) use (&$waits): void {
        $waits[] = (int) $duration->totalMilliseconds;
    });

    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(503)
        ->pushStatus(503)
        ->pushStatus(503)
        ->pushStatus(503)
        ->push(['data' => ['id' => 'pay_1']]);

    app('paymongo')->client()->get('/payments/pay_1');

    expect($waits)->toHaveCount(4)
        ->and($waits[0])->toBeBetween(100, 200)
        ->and($waits[1])->toBeBetween(200, 400)
        ->and($waits[2])->toBeBetween(400, 800)
        ->and($waits[3])->toBeBetween(500, 1000);
});

it('treats a 404 on a retried DELETE as the earlier attempt having deleted the resource', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(503)
        ->pushStatus(404);

    $response = app('paymongo')->client()->delete('/webhooks/hook_1');

    expect($response->status)->toBe(404)
        ->and($response->body)->toBe([]);

    Http::assertSentCount(2);
});

it('still throws for a 404 on a DELETE retried only after a rate limit', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->pushStatus(429)
        ->pushStatus(404);

    expect(fn () => app('paymongo')->client()->delete('/webhooks/hook_missing'))
        ->toThrow(ResourceNotFoundException::class);

    Http::assertSentCount(2);
});
