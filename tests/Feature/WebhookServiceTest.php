<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Webhook;
use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\Enums\WebhookStatus;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorPage;

it('creates a webhook normalizing enum events and maps the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('webhook'))]);

    $webhook = Paymongo::webhooks()->create('https://example.com/paymongo/webhook', [
        WebhookEventType::PaymentPaid,
        WebhookEventType::PaymentFailed,
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks'
        && $request->data() === ['data' => ['attributes' => [
            'url' => 'https://example.com/paymongo/webhook',
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

it('lists webhooks as a cursor page of DTOs', function () {
    $first = fixture_data('webhook')['data'];
    $second = fixture_data('webhook')['data'];
    $second['id'] = 'hook_Xy8dRfKmWq2LtNpZbCv7Gh4s';
    $second['attributes']['status'] = 'disabled';

    Http::fake(['api.paymongo.com/*' => Http::response(['data' => [$first, $second], 'has_more' => false])]);

    $webhooks = Paymongo::webhooks()->list();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks'
        && $request->body() === '');

    expect($webhooks)->toBeInstanceOf(CursorPage::class)
        ->and($webhooks)->toHaveCount(2)
        ->and($webhooks->hasMore)->toBeFalse()
        ->and($webhooks->items[0])->toBeInstanceOf(Webhook::class)
        ->and($webhooks->items[0]->id)->toBe('hook_Vq5cCzKFFV1yvhs8q1M4moJn')
        ->and($webhooks->items[1]->id)->toBe('hook_Xy8dRfKmWq2LtNpZbCv7Gh4s')
        ->and($webhooks->items[1]->status)->toBe(WebhookStatus::Disabled);
});

it('fetches the next page of webhooks after the last endpoint on the page', function () {
    $second = fixture_data('webhook')['data'];
    $second['id'] = 'hook_Xy8dRfKmWq2LtNpZbCv7Gh4s';

    Http::fakeSequence('api.paymongo.com/*')
        ->push(['data' => [fixture_data('webhook')['data']], 'has_more' => true])
        ->push(['data' => [$second], 'has_more' => false]);

    $next = Paymongo::webhooks()->list(['limit' => 1])->nextPage();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks?limit=1&after=hook_Vq5cCzKFFV1yvhs8q1M4moJn');

    expect($next?->items[0]->id)->toBe('hook_Xy8dRfKmWq2LtNpZbCv7Gh4s')
        ->and($next?->nextPage())->toBeNull();
});

it('deletes a webhook and returns true', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(null, 204)]);

    $deleted = Paymongo::webhooks()->delete('hook_Vq5cCzKFFV1yvhs8q1M4moJn');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'DELETE'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks/hook_Vq5cCzKFFV1yvhs8q1M4moJn'
        && $request->body() === '');

    expect($deleted)->toBeTrue();
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
        'url' => 'https://example.com/new-hook',
        'events' => ['payment.paid'],
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
        && $request->url() === 'https://api.paymongo.com/v1/webhooks/hook_Vq5cCzKFFV1yvhs8q1M4moJn'
        && $request->data() === ['data' => ['attributes' => [
            'url' => 'https://example.com/new-hook',
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
