<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Payout;
use Luigel\Paymongo\Data\PayoutSchedule;
use Luigel\Paymongo\Data\PayoutTransaction;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PayoutStatus;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorTokenPage;
use Luigel\Paymongo\Testing\Fixtures;

/**
 * A two-payout first page carrying a next cursor token.
 *
 * @return array<string, mixed>
 */
function payouts_first_page(): array
{
    return Fixtures::payoutList([
        Fixtures::payout(),
        Fixtures::payout(['id' => 'po_SecondPayoutId2345678901']),
    ], nextCursor: 'cursor_opaque_token_abc123');
}

/**
 * A single-payout last page without a next cursor.
 *
 * @return array<string, mixed>
 */
function payouts_second_page(): array
{
    return Fixtures::payoutList([
        Fixtures::payout(['id' => 'po_ThirdPayoutId3456789012']),
    ]);
}

it('lists payouts with the documented query parameters', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(payouts_first_page())]);

    $page = Paymongo::payouts()->list([
        'limit' => 10,
        'payout_status' => 'deposited',
        'provider' => 'paymongo_central_hub',
        'created_at.between' => '2024-09-01..2024-09-30',
        'sort_by' => 'created_at',
        'order' => 'desc',
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payouts?limit=10&payout_status=deposited&provider=paymongo_central_hub&created_at.between=2024-09-01..2024-09-30&sort_by=created_at&order=desc'
        && $request->body() === '');

    expect($page)->toBeInstanceOf(CursorTokenPage::class)
        ->and($page)->toHaveCount(2)
        ->and($page->first())->toBeInstanceOf(Payout::class)
        ->and($page->first()?->id)->toBe('po_2fdKBqNAKMvUXTUAvhZDdXbW')
        ->and($page->first()?->status)->toBe(PayoutStatus::Deposited)
        ->and($page->nextCursor)->toBe('cursor_opaque_token_abc123')
        ->and($page->prevCursor)->toBeNull();
});

it('follows the next_cursor token when fetching the next payout page', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->push(payouts_first_page())
        ->push(payouts_second_page());

    $page = Paymongo::payouts()->list(['limit' => 2]);

    $next = $page->nextPage();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/payouts?limit=2');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/payouts?limit=2&after=cursor_opaque_token_abc123');

    Http::assertSentCount(2);

    expect($next)->toBeInstanceOf(CursorTokenPage::class)
        ->and($next)->toHaveCount(1)
        ->and($next?->first()?->id)->toBe('po_ThirdPayoutId3456789012')
        ->and($next?->nextCursor)->toBeNull()
        ->and($next?->nextPage())->toBeNull();
});

it('exposes the totals metadata without the cursor tokens', function () {
    Http::fake(['api.paymongo.com/*' => Http::response([
        'data' => [Fixtures::payout()['data']],
        'pagination' => [
            'next_cursor' => 'cursor_next',
            'prev_cursor' => 'cursor_prev',
            'total_records' => 25,
            'total_amount' => 12500000,
            'total_per_currency' => ['PHP' => 12500000],
        ],
    ])]);

    $page = Paymongo::payouts()->list();

    expect($page->nextCursor)->toBe('cursor_next')
        ->and($page->prevCursor)->toBe('cursor_prev')
        ->and($page->meta)->toBe([
            'total_records' => 25,
            'total_amount' => 12500000,
            'total_per_currency' => ['PHP' => 12500000],
        ]);
});

it('retrieves a payout and maps the amounts onto the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::payout())]);

    $payout = Paymongo::payouts()->retrieve('po_2fdKBqNAKMvUXTUAvhZDdXbW');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payouts/po_2fdKBqNAKMvUXTUAvhZDdXbW'
        && $request->body() === '');

    expect($payout)->toBeInstanceOf(Payout::class)
        ->and($payout->id)->toBe('po_2fdKBqNAKMvUXTUAvhZDdXbW')
        ->and($payout->type)->toBe('payout')
        ->and($payout->amount)->toBe(500000)
        ->and($payout->netAmount)->toBe(485500)
        ->and($payout->fee)->toBe(14500)
        ->and($payout->taxAmount)->toBe(0)
        ->and($payout->refundAmount)->toBe(0)
        ->and($payout->disputeAmount)->toBe(0)
        ->and($payout->adjustmentAmount)->toBe(0)
        ->and($payout->currency)->toBe(Currency::PHP)
        ->and($payout->status)->toBe(PayoutStatus::Deposited)
        ->and($payout->bankAccountName)->toBe('Juan Dela Cruz')
        ->and($payout->bankAccountNumber)->toBe('****4567')
        ->and($payout->bankName)->toBe('BDO Unibank')
        ->and($payout->livemode)->toBeFalse()
        ->and($payout->money()?->format())->toBe('₱4,855.00');
});

it('lists payout transactions exposing the kind through the resource type', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::payoutList([Fixtures::payoutTransaction()]))]);

    $page = Paymongo::payouts()->transactions('po_2fdKBqNAKMvUXTUAvhZDdXbW', ['limit' => 5]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payouts/po_2fdKBqNAKMvUXTUAvhZDdXbW/transactions?limit=5'
        && $request->body() === '');

    expect($page)->toBeInstanceOf(CursorTokenPage::class)
        ->and($page)->toHaveCount(1)
        ->and($page->first())->toBeInstanceOf(PayoutTransaction::class)
        ->and($page->first()?->id)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi')
        ->and($page->first()?->type)->toBe('payment')
        ->and($page->first()?->transactionType())->toBe('payment')
        ->and($page->first()?->amount)->toBe(150050)
        ->and($page->first()?->currency)->toBe(Currency::PHP)
        ->and($page->first()?->netAmount)->toBe(144798)
        ->and($page->first()?->fee)->toBe(5252)
        ->and($page->nextCursor)->toBeNull();
});

it('retrieves the payout schedule of a merchant', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::payoutSchedule())]);

    $schedule = Paymongo::payouts()->schedule('org_9NxTZ8ZDVQpZC3bDMSKtwEXA');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/merchants/org_9NxTZ8ZDVQpZC3bDMSKtwEXA/schedules'
        && $request->body() === '');

    expect($schedule)->toBeInstanceOf(PayoutSchedule::class)
        ->and($schedule->scheduleType)->toBe('automatic')
        ->and($schedule->options)->toBe(['automatic', 'manual'])
        ->and($schedule->lineup)->toBe(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
});
