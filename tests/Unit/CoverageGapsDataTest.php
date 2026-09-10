<?php

declare(strict_types=1);

use Luigel\Paymongo\Data\CheckoutSession;
use Luigel\Paymongo\Data\LineItem;
use Luigel\Paymongo\Data\Link;
use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Data\PaymentLink;
use Luigel\Paymongo\Data\PayoutTransaction;
use Luigel\Paymongo\Testing\Fixtures;

it('maps a checkout session with a non-list line_items attribute to no line items', function () {
    $data = Fixtures::checkoutSession()['data'];
    $data['attributes']['line_items'] = 'unexpected';

    $session = CheckoutSession::fromArray($data);

    expect($session->lineItems)->toBe([])
        ->and($session->payments)->toHaveCount(1)
        ->and($session->attribute('line_items'))->toBe('unexpected');
});

it('maps a checkout session with a non-list payments attribute to no payments', function () {
    $data = Fixtures::checkoutSession()['data'];
    $data['attributes']['payments'] = 'unexpected';

    $session = CheckoutSession::fromArray($data);

    expect($session->payments)->toBe([])
        ->and($session->lineItems)->toHaveCount(2)
        ->and($session->attribute('payments'))->toBe('unexpected');
});

it('maps a line item with a non-list images value to no images', function () {
    $lineItem = LineItem::fromArray([
        'amount'   => 25000,
        'currency' => 'PHP',
        'name'     => 'Gift Wrap',
        'quantity' => 2,
        'images'   => 'https://images.example.com/gift-wrap.png',
    ]);

    expect($lineItem->images)->toBe([])
        ->and($lineItem->name)->toBe('Gift Wrap')
        ->and($lineItem->quantity)->toBe(2)
        ->and($lineItem->money()?->format())->toBe('₱250.00');
});

it('maps a link with a non-list payments attribute to no payments', function () {
    $data = Fixtures::link()['data'];
    $data['attributes']['payments'] = 'unexpected';

    $link = Link::fromArray($data);

    expect($link->payments)->toBe([])
        ->and($link->attribute('payments'))->toBe('unexpected');
});

it('skips non-array entries in a link payments list', function () {
    $data = Fixtures::link()['data'];
    $data['attributes']['payments'] = [
        'unexpected',
        42,
        null,
        ['data' => Fixtures::payment()['data']],
    ];

    $link = Link::fromArray($data);

    expect($link->payments)->toHaveCount(1)
        ->and($link->payments[0])->toBeInstanceOf(Payment::class)
        ->and($link->payments[0]->id)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi');
});

it('maps non-string and empty payment link timestamps to null', function () {
    $link = PaymentLink::fromArray([
        'id'         => 'plink_timestamps',
        'created_at' => 1725840000,
        'updated_at' => '',
    ]);

    expect($link->createdAt)->toBeNull()
        ->and($link->updatedAt)->toBeNull()
        ->and($link->attribute('created_at'))->toBe(1725840000);
});

it('maps unparsable payment link timestamps to null', function () {
    $link = PaymentLink::fromArray([
        'id'         => 'plink_timestamps',
        'created_at' => 'not a date',
        'updated_at' => '2024-09-09T00:00:00.000Z',
    ]);

    expect($link->createdAt)->toBeNull()
        ->and($link->updatedAt?->getTimestamp())->toBe(1725840000)
        ->and($link->attribute('created_at'))->toBe('not a date');
});

it('exposes a payout transaction amount as money', function () {
    $transaction = PayoutTransaction::fromArray(Fixtures::payoutTransaction()['data']);

    expect($transaction->money()?->centavos())->toBe(150050)
        ->and($transaction->money()?->format())->toBe('₱1,500.50');
});

it('returns null money for a payout transaction without an amount', function () {
    $transaction = PayoutTransaction::fromArray(['id' => 'pay_x', 'type' => 'payment', 'attributes' => []]);

    expect($transaction->amount)->toBeNull()
        ->and($transaction->money())->toBeNull();
});
