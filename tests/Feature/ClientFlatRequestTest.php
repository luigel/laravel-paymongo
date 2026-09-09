<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('sends the flat POST body verbatim without the data envelope', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'plink_1']])]);

    app('paymongo')->client()->postFlat('/payment_links', [
        'amount' => 150050,
        'currency' => 'PHP',
        'description' => 'Order #10101',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/payment_links'
            && ! array_key_exists('data', $request->data())
            && $request->data() === [
                'amount' => 150050,
                'currency' => 'PHP',
                'description' => 'Order #10101',
            ];
    });
});

it('sends no body on a flat POST with an empty body but keeps idempotency', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'qr_1']])]);

    app('paymongo')->client()->postFlat('/qr/qr_1/expire');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->body() === ''
            && Str::isUuid($request->header('Idempotency-Key')[0] ?? '');
    });
});

it('prefers an explicit idempotency key on a flat POST', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'plink_1']])]);

    app('paymongo')->client()->postFlat('/payment_links', ['amount' => 10000], 'plink-order-42');

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Idempotency-Key', 'plink-order-42');
    });
});

it('normalizes backed enums recursively in flat bodies', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'qr_1']])]);

    app('paymongo')->client()->postFlat('/qr/mpm/generate', [
        'mode' => ClientFlatRequestTestMode::P2m,
        'metadata' => ['kind' => ClientFlatRequestTestMode::P2p],
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->data() === [
            'mode' => 'p2m',
            'metadata' => ['kind' => 'p2p'],
        ];
    });
});

it('sends the flat PATCH body verbatim without an idempotency key', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'plink_1']])]);

    app('paymongo')->client()->patchFlat('/payment_links/plink_1', ['status' => 'archived']);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'PATCH'
            && $request->url() === 'https://api.paymongo.com/v1/payment_links/plink_1'
            && ! array_key_exists('data', $request->data())
            && $request->data() === ['status' => 'archived']
            && ! $request->hasHeader('Idempotency-Key');
    });
});

it('sends no body on a flat PATCH with an empty body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'plink_1']])]);

    app('paymongo')->client()->patchFlat('/payment_links/plink_1');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'PATCH' && $request->body() === '';
    });
});

it('leaves the envelope methods untouched by the flat variants', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'pi_1']])]);

    app('paymongo')->client()->post('/payment_intents', ['amount' => 10000]);

    Http::assertSent(function (Request $request): bool {
        return $request->data() === ['data' => ['attributes' => ['amount' => 10000]]];
    });
});

it('uses an absolute URL verbatim, bypassing the configured base URL', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'qr_1']])]);

    $client = app('paymongo')->client();
    $client->get('https://api.paymongo.com/v3/qr/qr_1', ['qr_string' => 'true']);
    $client->postFlat('https://api.paymongo.com/v3/qr/mpm/generate', ['mode' => 'p2m']);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v3/qr/qr_1?qr_string=true';
    });

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v3/qr/mpm/generate'
            && $request->data() === ['mode' => 'p2m'];
    });
});

enum ClientFlatRequestTestMode: string
{
    case P2p = 'p2p';
    case P2m = 'p2m';
}
