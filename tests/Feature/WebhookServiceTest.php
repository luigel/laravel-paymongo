<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Webhook;
use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\Enums\WebhookStatus;
use Luigel\Paymongo\Facades\Paymongo;

it('creates a webhook normalizing enum events and maps the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('webhook'))]);

    $webhook = Paymongo::webhooks()->create('https://example.com/paymongo/webhook', [
        WebhookEventType::PaymentPaid,
        WebhookEventType::PaymentFailed,
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks'
        && $request->data() === ['data' => ['attributes' => [
            'url'    => 'https://example.com/paymongo/webhook',
            'events' => ['payment.paid', 'payment.failed'],
        ]]]);

    expect($webhook)->toBeInstanceOf(Webhook::class)
        ->and($webhook->id)->toBe('hook_Vq5cCzKFFV1yvhs8q1M4moJn')
        ->and($webhook->type)->toBe('webhook')
        ->and($webhook->url)->toBe('https://example.com/paymongo/webhook')
        ->and($webhook->status)->toBe(WebhookStatus::Enabled)
        ->and($webhook->secretKey)->toBe('whsk_2rmFbq9EDdEL95JQ6sauCbBz')
        ->and($webhook->livemode)->toBeFalse()
        ->and($webhook->events)->toBe(['payment.paid', 'payment.failed']);
});

it('lists webhooks as an array of DTOs', function () {
    $first = fixture_data('webhook')['data'];
    $second = fixture_data('webhook')['data'];
    $second['id'] = 'hook_Xy8dRfKmWq2LtNpZbCv7Gh4s';
    $second['attributes']['status'] = 'disabled';

    Http::fake(['api.paymongo.com/*' => Http::response(['data' => [$first, $second]])]);

    $webhooks = Paymongo::webhooks()->list();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks'
        && $request->body() === '');

    expect($webhooks)->toBeArray()
        ->and($webhooks)->toHaveCount(2)
        ->and($webhooks[0])->toBeInstanceOf(Webhook::class)
        ->and($webhooks[0]->id)->toBe('hook_Vq5cCzKFFV1yvhs8q1M4moJn')
        ->and($webhooks[1]->id)->toBe('hook_Xy8dRfKmWq2LtNpZbCv7Gh4s')
        ->and($webhooks[1]->status)->toBe(WebhookStatus::Disabled);
});

it('retrieves a webhook', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('webhook'))]);

    $webhook = Paymongo::webhooks()->retrieve('hook_Vq5cCzKFFV1yvhs8q1M4moJn');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks/hook_Vq5cCzKFFV1yvhs8q1M4moJn'
        && $request->body() === '');

    expect($webhook->id)->toBe('hook_Vq5cCzKFFV1yvhs8q1M4moJn');
});

it('updates a webhook via PUT with the envelope', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('webhook'))]);

    Paymongo::webhooks()->update('hook_Vq5cCzKFFV1yvhs8q1M4moJn', [
        'url'    => 'https://example.com/new-hook',
        'events' => ['payment.paid'],
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks/hook_Vq5cCzKFFV1yvhs8q1M4moJn'
        && $request->data() === ['data' => ['attributes' => [
            'url'    => 'https://example.com/new-hook',
            'events' => ['payment.paid'],
        ]]]);
});

it('enables a webhook with an empty POST body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('webhook'))]);

    Paymongo::webhooks()->enable('hook_Vq5cCzKFFV1yvhs8q1M4moJn');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks/hook_Vq5cCzKFFV1yvhs8q1M4moJn/enable'
        && $request->body() === '');
});

it('disables a webhook with an empty POST body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('webhook'))]);

    Paymongo::webhooks()->disable('hook_Vq5cCzKFFV1yvhs8q1M4moJn');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks/hook_Vq5cCzKFFV1yvhs8q1M4moJn/disable'
        && $request->body() === '');
});
