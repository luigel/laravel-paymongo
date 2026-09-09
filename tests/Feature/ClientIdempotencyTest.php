<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('auto-generates a UUID idempotency key on POST', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'pi_1']])]);

    app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]);

    Http::assertSent(function (Request $request): bool {
        return Str::isUuid($request->header('Idempotency-Key')[0] ?? '');
    });
});

it('sends the idempotency key on a POST without a body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'pi_1']])]);

    app('paymongo')->client()->post('/payment_intents/pi_1/cancel');

    Http::assertSent(function (Request $request): bool {
        return $request->body() === ''
            && Str::isUuid($request->header('Idempotency-Key')[0] ?? '');
    });
});

it('prefers an explicit idempotency key over the auto-generated one', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'pi_1']])]);

    app('paymongo')->client()->post('/payment_intents', ['amount' => 10000], 'order-42-attempt-1');

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Idempotency-Key', 'order-42-attempt-1');
    });
});

it('sends no idempotency key when auto idempotency is disabled', function () {
    config()->set('paymongo.idempotency.auto', false);

    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'pi_1']])]);

    app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]);

    Http::assertSent(function (Request $request): bool {
        return ! $request->hasHeader('Idempotency-Key');
    });
});

it('still sends an explicit idempotency key when auto idempotency is disabled', function () {
    config()->set('paymongo.idempotency.auto', false);

    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'pi_1']])]);

    app('paymongo')->client()->post('/payment_intents', ['amount' => 10000], 'order-42-attempt-1');

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Idempotency-Key', 'order-42-attempt-1');
    });
});

it('never sends an idempotency key on GET, PUT, PATCH, or DELETE', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => []])]);

    $client = app('paymongo')->client();
    $client->get('/payments');
    $client->put('/webhooks/hook_1', ['url' => 'https://example.com/hook']);
    $client->patch('/customers/cus_1', ['first_name' => 'Juan']);
    $client->delete('/customers/cus_1');

    Http::assertSentCount(4);

    expect(Http::recorded(
        fn (Request $request): bool => $request->hasHeader('Idempotency-Key')
    ))->toBeEmpty();
});
