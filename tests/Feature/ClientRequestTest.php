<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('wraps POST attributes in the data.attributes envelope', function () {
    Http::fake([
        'api.paymongo.com/*' => Http::response([
            'data' => ['id' => 'pi_123', 'type' => 'payment_intent', 'attributes' => ['amount' => 10000]],
        ]),
    ]);

    $response = app('paymongo')->client()->post('/payment_intents', [
        'amount' => 10000,
        'currency' => 'PHP',
    ]);

    expect($response->status)->toBe(200)
        ->and($response->data())->toBe(['id' => 'pi_123', 'type' => 'payment_intent', 'attributes' => ['amount' => 10000]])
        ->and($response->isList())->toBeFalse()
        ->and($response->hasMore())->toBeFalse();

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/payment_intents'
            && $request->data() === ['data' => ['attributes' => ['amount' => 10000, 'currency' => 'PHP']]];
    });
});

it('sends no body when POST attributes are empty', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'pi_123']])]);

    app('paymongo')->client()->post('/payment_intents/pi_123/cancel');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/payment_intents/pi_123/cancel'
            && $request->body() === '';
    });
});

it('sends GET query parameters without a body', function () {
    Http::fake([
        'api.paymongo.com/*' => Http::response([
            'data' => [['id' => 'pay_1'], ['id' => 'pay_2']],
            'has_more' => true,
        ]),
    ]);

    $response = app('paymongo')->client()->get('/payments', ['limit' => 10, 'after' => 'pay_0']);

    expect($response->data())->toHaveCount(2)
        ->and($response->isList())->toBeTrue()
        ->and($response->hasMore())->toBeTrue();

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v1/payments?limit=10&after=pay_0'
            && $request->body() === '';
    });
});

it('wraps PUT and PATCH attributes in the envelope', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'x']])]);

    $client = app('paymongo')->client();
    $client->put('/webhooks/hook_1', ['url' => 'https://example.com/hook']);
    $client->patch('/customers/cus_1', ['first_name' => 'Juan']);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'PUT'
            && $request->url() === 'https://api.paymongo.com/v1/webhooks/hook_1'
            && $request->data() === ['data' => ['attributes' => ['url' => 'https://example.com/hook']]];
    });

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'PATCH'
            && $request->url() === 'https://api.paymongo.com/v1/customers/cus_1'
            && $request->data() === ['data' => ['attributes' => ['first_name' => 'Juan']]];
    });
});

it('sends DELETE requests without a body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['deleted' => true]])]);

    app('paymongo')->client()->delete('/customers/cus_1');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://api.paymongo.com/v1/customers/cus_1'
            && $request->body() === '';
    });
});

it('authenticates with the secret key as basic auth username', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => []])]);

    app('paymongo')->client()->get('/payments');

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Authorization', 'Basic '.base64_encode('sk_test_fake:'));
    });
});

it('sends the exact package user agent', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => []])]);

    app('paymongo')->client()->get('/payments');

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('User-Agent', 'luigel/laravel-paymongo v3 (php '.PHP_VERSION.')');
    });
});

it('sends and accepts JSON', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => []])]);

    app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]);

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Content-Type', 'application/json')
            && $request->hasHeader('Accept', 'application/json');
    });
});

it('normalizes backed enums recursively in outgoing payloads', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => []])]);

    app('paymongo')->client()->post('/payment_intents', [
        'capture_type' => ClientRequestTestCaptureType::Manual,
        'payment_method_allowed' => [ClientRequestTestMethod::Card, ClientRequestTestMethod::Gcash],
        'metadata' => [
            'nested' => ['method' => ClientRequestTestMethod::Card],
            'priority' => ClientRequestTestPriority::High,
        ],
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->data() === ['data' => ['attributes' => [
            'capture_type' => 'manual',
            'payment_method_allowed' => ['card', 'gcash'],
            'metadata' => [
                'nested' => ['method' => 'card'],
                'priority' => 5,
            ],
        ]]];
    });
});

enum ClientRequestTestMethod: string
{
    case Card = 'card';
    case Gcash = 'gcash';
}

enum ClientRequestTestCaptureType: string
{
    case Manual = 'manual';
}

enum ClientRequestTestPriority: int
{
    case High = 5;
}
