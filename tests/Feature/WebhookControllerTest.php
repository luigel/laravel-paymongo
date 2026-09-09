<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Luigel\Paymongo\Events\PaymentPaid;
use Luigel\Paymongo\Events\WebhookReceived;
use Luigel\Paymongo\Http\Controllers\WebhookController;

beforeEach(function () {
    // The controller is exercised in isolation; signature checks are covered
    // by the middleware tests.
    Route::post('/hook', WebhookController::class);
});

it('dispatches the generic event and the mapped typed event', function () {
    Event::fake();

    $this->postJson('/hook', fixture_data('webhook_event'))
        ->assertOk()
        ->assertExactJson(['received' => true]);

    Event::assertDispatched(WebhookReceived::class, function (WebhookReceived $event): bool {
        return $event->event->id === 'evt_Jk8VbF2c9sQmXhT4wLpNyRd6'
            && $event->event->type === 'payment.paid';
    });

    Event::assertDispatched(PaymentPaid::class, function (PaymentPaid $event): bool {
        return $event->event->resourceId() === 'pay_hvTn9EyxduZ9gV8WHhSGYqBi'
            && $event->event->resourceAttribute('amount') === 150050;
    });
});

it('dispatches only the generic event for unknown event types', function () {
    Event::fake();

    $payload = fixture_data('webhook_event');
    $payload['data']['attributes']['type'] = 'payment.superseded';

    $this->postJson('/hook', $payload)
        ->assertOk()
        ->assertExactJson(['received' => true]);

    Event::assertDispatched(WebhookReceived::class);
    Event::assertNotDispatched(PaymentPaid::class);
});

it('suppresses a duplicate delivery of the same event id', function () {
    Event::fake();

    $payload = fixture_data('webhook_event');

    $this->postJson('/hook', $payload)->assertOk()->assertExactJson(['received' => true]);
    $this->postJson('/hook', $payload)->assertOk()->assertExactJson(['received' => true]);

    Event::assertDispatchedTimes(WebhookReceived::class, 1);
    Event::assertDispatchedTimes(PaymentPaid::class, 1);
});

it('dispatches every delivery when dedupe is disabled', function () {
    config()->set('paymongo.webhooks.dedupe.enabled', false);

    Event::fake();

    $payload = fixture_data('webhook_event');

    $this->postJson('/hook', $payload)->assertOk();
    $this->postJson('/hook', $payload)->assertOk();

    Event::assertDispatchedTimes(WebhookReceived::class, 2);
    Event::assertDispatchedTimes(PaymentPaid::class, 2);
});

it('remembers seen event ids in the configured cache store', function () {
    config()->set('cache.stores.paymongo_dedupe', ['driver' => 'array']);
    config()->set('paymongo.webhooks.dedupe.store', 'paymongo_dedupe');

    Event::fake();

    $this->postJson('/hook', fixture_data('webhook_event'))->assertOk();

    expect(Cache::store('paymongo_dedupe')->has('paymongo:webhook:evt_Jk8VbF2c9sQmXhT4wLpNyRd6'))->toBeTrue()
        ->and(Cache::store()->has('paymongo:webhook:evt_Jk8VbF2c9sQmXhT4wLpNyRd6'))->toBeFalse();
});
