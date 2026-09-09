<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Data\PaymentIntent;
use Luigel\Paymongo\Data\Shared\NextAction;

it('reads raw attributes with dot notation and a default', function () {
    $intent = PaymentIntent::fromArray(fixture_data('payment_intent')['data']);

    expect($intent->attribute('status'))->toBe('awaiting_payment_method')
        ->and($intent->attribute('metadata.order_id'))->toBe('10101')
        ->and($intent->attribute('payment_method_options.card.request_three_d_secure'))->toBe('any')
        ->and($intent->attribute('does.not.exist'))->toBeNull()
        ->and($intent->attribute('does.not.exist', 'fallback'))->toBe('fallback');
});

it('round-trips id, type and attributes through toArray', function () {
    $data = fixture_data('payment_intent')['data'];
    $intent = PaymentIntent::fromArray($data);

    expect($intent->toArray())->toBe([
        'id' => $data['id'],
        'type' => $data['type'],
        'attributes' => $data['attributes'],
    ]);
});

it('converts unix timestamps into CarbonImmutable instances', function () {
    $intent = PaymentIntent::fromArray(fixture_data('payment_intent')['data']);

    expect($intent->createdAt())->toBeInstanceOf(CarbonImmutable::class)
        ->and($intent->createdAt()?->getTimestamp())->toBe(1725840000)
        ->and($intent->updatedAt()?->getTimestamp())->toBe(1725840000);
});

it('returns null timestamps when the attributes are missing', function () {
    $intent = PaymentIntent::fromArray(['id' => 'pi_x', 'type' => 'payment_intent', 'attributes' => []]);

    expect($intent->createdAt())->toBeNull()
        ->and($intent->updatedAt())->toBeNull();
});

it('maps unknown enum values to null while keeping the raw attribute', function () {
    $data = fixture_data('payment_intent')['data'];
    $data['attributes']['status'] = 'a_future_status';
    $data['attributes']['capture_type'] = 'a_future_capture_type';

    $intent = PaymentIntent::fromArray($data);

    expect($intent->status)->toBeNull()
        ->and($intent->attribute('status'))->toBe('a_future_status')
        ->and($intent->captureType)->toBeNull()
        ->and($intent->attribute('capture_type'))->toBe('a_future_capture_type');
});

it('tolerates empty attributes with null and default-valued properties', function () {
    $intent = PaymentIntent::fromArray(['id' => 'pi_x', 'type' => 'payment_intent', 'attributes' => []]);

    expect($intent->id)->toBe('pi_x')
        ->and($intent->amount)->toBeNull()
        ->and($intent->currency)->toBeNull()
        ->and($intent->status)->toBeNull()
        ->and($intent->livemode)->toBeFalse()
        ->and($intent->paymentMethodAllowed)->toBe([])
        ->and($intent->payments)->toBe([])
        ->and($intent->nextAction)->toBeNull()
        ->and($intent->metadata)->toBeNull()
        ->and($intent->money())->toBeNull();
});

it('maps the nested payments list onto Payment DTOs', function () {
    $data = fixture_data('payment_intent')['data'];
    $data['attributes']['status'] = 'succeeded';
    $data['attributes']['payments'] = [fixture_data('payment')['data']];

    $intent = PaymentIntent::fromArray($data);

    expect($intent->payments)->toHaveCount(1)
        ->and($intent->payments[0])->toBeInstanceOf(Payment::class)
        ->and($intent->payments[0]->id)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi')
        ->and($intent->payments[0]->amount)->toBe(150050);
});

it('maps next_action onto a NextAction DTO flattening the redirect', function () {
    $data = fixture_data('payment_intent')['data'];
    $data['attributes']['status'] = 'awaiting_next_action';
    $data['attributes']['next_action'] = [
        'type' => 'redirect',
        'redirect' => [
            'url' => 'https://test-sources.paymongo.com/sources?id=src_123',
            'return_url' => 'https://example.com/return',
        ],
    ];

    $intent = PaymentIntent::fromArray($data);

    expect($intent->nextAction)->toBeInstanceOf(NextAction::class)
        ->and($intent->nextAction?->type)->toBe('redirect')
        ->and($intent->nextAction?->url)->toBe('https://test-sources.paymongo.com/sources?id=src_123')
        ->and($intent->nextAction?->returnUrl)->toBe('https://example.com/return');
});
