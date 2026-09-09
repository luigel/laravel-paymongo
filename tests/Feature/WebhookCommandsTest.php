<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('creates a webhook subscribed to the default events', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('webhook'))]);

    $this->artisan('paymongo:webhook:create', ['url' => 'https://example.com/paymongo/webhook'])
        ->expectsOutputToContain('hook_Vq5cCzKFFV1yvhs8q1M4moJn')
        ->expectsOutputToContain('whsk_2rmFbq9EDdEL95JQ6sauCbBz')
        ->expectsOutputToContain('enabled')
        ->assertSuccessful();

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/webhooks'
            && $request->data() === ['data' => ['attributes' => [
                'url' => 'https://example.com/paymongo/webhook',
                'events' => ['payment.paid', 'payment.failed'],
            ]]];
    });
});

it('creates a webhook subscribed to explicitly passed events', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('webhook'))]);

    $this->artisan('paymongo:webhook:create', [
        'url' => 'https://example.com/paymongo/webhook',
        '--event' => ['source.chargeable', 'payment.refunded'],
    ])->assertSuccessful();

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/webhooks'
            && $request->data() === ['data' => ['attributes' => [
                'url' => 'https://example.com/paymongo/webhook',
                'events' => ['source.chargeable', 'payment.refunded'],
            ]]];
    });
});

it('lists webhooks as a table', function () {
    $first = fixture_data('webhook')['data'];
    $second = fixture_data('webhook')['data'];
    $second['id'] = 'hook_Xy8dRfKmWq2LtNpZbCv7Gh4s';
    $second['attributes']['url'] = 'https://example.com/other-hook';
    $second['attributes']['status'] = 'disabled';
    $second['attributes']['events'] = ['payment.paid'];

    Http::fake(['api.paymongo.com/*' => Http::response(['data' => [$first, $second]])]);

    $this->artisan('paymongo:webhook:list')
        ->expectsTable(['ID', 'URL', 'Status', 'Events'], [
            ['hook_Vq5cCzKFFV1yvhs8q1M4moJn', 'https://example.com/paymongo/webhook', 'enabled', 'payment.paid, payment.failed'],
            ['hook_Xy8dRfKmWq2LtNpZbCv7Gh4s', 'https://example.com/other-hook', 'disabled', 'payment.paid'],
        ])
        ->assertSuccessful();

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v1/webhooks';
    });
});

it('reports when no webhooks are registered', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => []])]);

    $this->artisan('paymongo:webhook:list')
        ->expectsOutputToContain('No webhooks are registered.')
        ->assertSuccessful();
});

it('enables a webhook with --enable', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('webhook'))]);

    $this->artisan('paymongo:webhook:toggle', ['id' => 'hook_Vq5cCzKFFV1yvhs8q1M4moJn', '--enable' => true])
        ->expectsOutputToContain('is now enabled')
        ->assertSuccessful();

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/webhooks/hook_Vq5cCzKFFV1yvhs8q1M4moJn/enable'
            && $request->body() === '';
    });
});

it('disables a webhook with --disable', function () {
    $disabled = fixture_data('webhook');
    $disabled['data']['attributes']['status'] = 'disabled';

    Http::fake(['api.paymongo.com/*' => Http::response($disabled)]);

    $this->artisan('paymongo:webhook:toggle', ['id' => 'hook_Vq5cCzKFFV1yvhs8q1M4moJn', '--disable' => true])
        ->expectsOutputToContain('is now disabled')
        ->assertSuccessful();

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/webhooks/hook_Vq5cCzKFFV1yvhs8q1M4moJn/disable'
            && $request->body() === '';
    });
});

it('errors when both --enable and --disable are passed', function () {
    Http::fake();

    $this->artisan('paymongo:webhook:toggle', [
        'id' => 'hook_Vq5cCzKFFV1yvhs8q1M4moJn',
        '--enable' => true,
        '--disable' => true,
    ])
        ->expectsOutputToContain('exactly one of --enable or --disable')
        ->assertFailed();

    Http::assertNothingSent();
});

it('errors when neither --enable nor --disable is passed', function () {
    Http::fake();

    $this->artisan('paymongo:webhook:toggle', ['id' => 'hook_Vq5cCzKFFV1yvhs8q1M4moJn'])
        ->expectsOutputToContain('exactly one of --enable or --disable')
        ->assertFailed();

    Http::assertNothingSent();
});
