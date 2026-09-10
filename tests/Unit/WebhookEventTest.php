<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\Webhooks\WebhookEvent;

it('parses a full event envelope', function () {
    $payload = fixture_data('webhook_event');

    $event = WebhookEvent::fromArray($payload);

    expect($event->id)->toBe('evt_Jk8VbF2c9sQmXhT4wLpNyRd6')
        ->and($event->type)->toBe('payment.paid')
        ->and($event->livemode)->toBeFalse()
        ->and($event->data)->toBe($payload['data']['attributes']['data'])
        ->and($event->timestamp)->toBeInstanceOf(CarbonImmutable::class)
        ->and($event->timestamp?->getTimestamp())->toBe(1725840060)
        ->and($event->raw)->toBe($payload);
});

it('resolves the event type enum and resource accessors', function () {
    $event = WebhookEvent::fromArray(fixture_data('webhook_event'));

    expect($event->eventType())->toBe(WebhookEventType::PaymentPaid)
        ->and($event->resourceId())->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi')
        ->and($event->resourceAttribute('amount'))->toBe(150050)
        ->and($event->resourceAttribute('billing.address.city'))->toBe('Taguig')
        ->and($event->resourceAttribute('status'))->toBe('paid')
        ->and($event->resourceAttribute('does_not_exist', 'fallback'))->toBe('fallback');
});

it('returns a null enum for unknown event names while keeping the raw type', function () {
    $payload = fixture_data('webhook_event');
    $payload['data']['attributes']['type'] = 'payment.superseded';

    $event = WebhookEvent::fromArray($payload);

    expect($event->type)->toBe('payment.superseded')
        ->and($event->eventType())->toBeNull();
});

it('tolerates an empty payload', function () {
    $event = WebhookEvent::fromArray([]);

    expect($event->id)->toBe('')
        ->and($event->type)->toBe('')
        ->and($event->livemode)->toBeFalse()
        ->and($event->data)->toBe([])
        ->and($event->timestamp)->toBeNull()
        ->and($event->raw)->toBe([])
        ->and($event->eventType())->toBeNull()
        ->and($event->resourceId())->toBeNull()
        ->and($event->resourceAttribute('anything'))->toBeNull();
});

it('tolerates malformed envelope values', function () {
    $event = WebhookEvent::fromArray([
        'data' => [
            'id' => 12345,
            'attributes' => [
                'type' => ['not-a-string'],
                'livemode' => 1,
                'data' => 'not-an-array',
                'created_at' => 'not-an-int',
            ],
        ],
    ]);

    expect($event->id)->toBe('')
        ->and($event->type)->toBe('')
        ->and($event->livemode)->toBeTrue()
        ->and($event->data)->toBe([])
        ->and($event->timestamp)->toBeNull()
        ->and($event->resourceId())->toBeNull();
});
