<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Refund;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\RefundReason;
use Luigel\Paymongo\Enums\RefundStatus;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorPage;

it('creates a refund normalizing the enum reason and maps the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('refund'))]);

    $refund = Paymongo::refunds()->create([
        'amount' => 50000,
        'payment_id' => 'pay_hvTn9EyxduZ9gV8WHhSGYqBi',
        'reason' => RefundReason::Duplicate,
        'notes' => 'Customer returned the item.',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/refunds'
            && $request->data() === ['data' => ['attributes' => [
                'amount' => 50000,
                'payment_id' => 'pay_hvTn9EyxduZ9gV8WHhSGYqBi',
                'reason' => 'duplicate',
                'notes' => 'Customer returned the item.',
            ]]]
            && $request->hasHeader('Idempotency-Key');
    });

    expect($refund)->toBeInstanceOf(Refund::class)
        ->and($refund->id)->toBe('ref_9K2Wf3mLpQvXsTzYbNcVdGhJ')
        ->and($refund->type)->toBe('refund')
        ->and($refund->amount)->toBe(50000)
        ->and($refund->currency)->toBe(Currency::PHP)
        ->and($refund->reason)->toBe(RefundReason::Duplicate)
        ->and($refund->status)->toBe(RefundStatus::Pending)
        ->and($refund->paymentId)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi')
        ->and($refund->notes)->toBe('Customer returned the item.')
        ->and($refund->livemode)->toBeFalse()
        ->and($refund->metadata)->toBe(['requested_by' => 'support'])
        ->and($refund->refundedAt())->toBeNull()
        ->and($refund->createdAt()?->getTimestamp())->toBe(1725926400)
        ->and($refund->money()?->toDecimal())->toBe('500.00');
});

it('sends an explicit idempotency key when creating', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('refund'))]);

    Paymongo::refunds()->create(['amount' => 50000, 'payment_id' => 'pay_x', 'reason' => 'others'], 'ref-idem-9');

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Idempotency-Key', 'ref-idem-9');
    });
});

it('retrieves a refund', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('refund'))]);

    $refund = Paymongo::refunds()->retrieve('ref_9K2Wf3mLpQvXsTzYbNcVdGhJ');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v1/refunds/ref_9K2Wf3mLpQvXsTzYbNcVdGhJ'
            && $request->body() === '';
    });

    expect($refund->id)->toBe('ref_9K2Wf3mLpQvXsTzYbNcVdGhJ');
});

it('lists refunds with the payment_id filter in the query', function () {
    Http::fake([
        'api.paymongo.com/*' => Http::response([
            'data' => [fixture_data('refund')['data']],
            'has_more' => false,
        ]),
    ]);

    $page = Paymongo::refunds()->list(['limit' => 5, 'payment_id' => 'pay_hvTn9EyxduZ9gV8WHhSGYqBi']);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v1/refunds?limit=5&payment_id=pay_hvTn9EyxduZ9gV8WHhSGYqBi'
            && $request->body() === '';
    });

    expect($page)->toBeInstanceOf(CursorPage::class)
        ->and($page)->toHaveCount(1)
        ->and($page->hasMore)->toBeFalse()
        ->and($page->first())->toBeInstanceOf(Refund::class)
        ->and($page->nextPage())->toBeNull();
});
