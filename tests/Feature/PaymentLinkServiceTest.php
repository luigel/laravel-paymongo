<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Data\PaymentLink;
use Luigel\Paymongo\Data\Refund;
use Luigel\Paymongo\Enums\PaymentLinkStatus;
use Luigel\Paymongo\Enums\PaymentStatus;
use Luigel\Paymongo\Enums\RefundReason;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorPage;
use Luigel\Paymongo\Testing\Fixtures;

/**
 * A two-link flat list body for the first page of a paginated listing.
 *
 * @return array<string, mixed>
 */
function payment_links_first_page(): array
{
    return Fixtures::flatList([
        Fixtures::paymentLink(),
        Fixtures::paymentLink(['id' => 'plink_SecondFlatLink2345678901', 'status' => 'archived']),
    ], hasMore: true);
}

/**
 * A single-link flat list body for the second page.
 *
 * @return array<string, mixed>
 */
function payment_links_second_page(): array
{
    return Fixtures::flatList([
        Fixtures::paymentLink(['id' => 'plink_ThirdFlatLink3456789012']),
    ]);
}

it('creates a payment link with a flat body and an idempotency key', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::paymentLink())]);

    $link = Paymongo::paymentLinks()->create([
        'amount' => 150050,
        'currency' => 'PHP',
        'description' => 'Payment for Order #10101',
        'remarks' => 'Facebook order',
        'restrictions' => ['completed_sessions' => 1],
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payment_links'
        && ! array_key_exists('data', $request->data())
        && $request->data() === [
            'amount' => 150050,
            'currency' => 'PHP',
            'description' => 'Payment for Order #10101',
            'remarks' => 'Facebook order',
            'restrictions' => ['completed_sessions' => 1],
        ]
        && Str::isUuid($request->header('Idempotency-Key')[0] ?? ''));

    expect($link)->toBeInstanceOf(PaymentLink::class)
        ->and($link->id)->toBe('plink_uSJXoxTBNqRrg35kj5w9dTVY')
        ->and($link->amount)->toBe(150050)
        ->and($link->currency)->toBe('PHP')
        ->and($link->description)->toBe('Payment for Order #10101')
        ->and($link->remarks)->toBe('Facebook order')
        ->and($link->status)->toBe(PaymentLinkStatus::Active)
        ->and($link->livemode)->toBeFalse()
        ->and($link->url)->toBe('https://pm.link/luigel-test/plink_uSJXoxTBNqRrg35kj5w9dTVY')
        ->and($link->referenceNumber)->toBe('JCUV9NF')
        ->and($link->metadata)->toBeNull()
        ->and($link->restrictions)->toBe(['completed_sessions' => 1])
        ->and($link->money()?->format())->toBe('₱1,500.50')
        ->and($link->createdAt?->getTimestamp())->toBe(1725840000)
        ->and($link->updatedAt?->getTimestamp())->toBe(1725840000)
        ->and($link->attribute('restrictions.completed_sessions'))->toBe(1);
});

it('passes an explicit idempotency key through to the flat create', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::paymentLink())]);

    Paymongo::paymentLinks()->create(['amount' => 150050, 'currency' => 'PHP'], 'plink-order-42');

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('Idempotency-Key', 'plink-order-42'));
});

it('retrieves a payment link', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::paymentLink())]);

    $link = Paymongo::paymentLinks()->retrieve('plink_uSJXoxTBNqRrg35kj5w9dTVY');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payment_links/plink_uSJXoxTBNqRrg35kj5w9dTVY'
        && $request->body() === '');

    expect($link->id)->toBe('plink_uSJXoxTBNqRrg35kj5w9dTVY');
});

it('updates a payment link with a flat PATCH body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::paymentLink(['description' => 'Updated order']))]);

    $link = Paymongo::paymentLinks()->update('plink_uSJXoxTBNqRrg35kj5w9dTVY', [
        'description' => 'Updated order',
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PATCH'
        && $request->url() === 'https://api.paymongo.com/v1/payment_links/plink_uSJXoxTBNqRrg35kj5w9dTVY'
        && ! array_key_exists('data', $request->data())
        && $request->data() === ['description' => 'Updated order']);

    expect($link->description)->toBe('Updated order');
});

it('archives a payment link through a status-only flat PATCH', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::paymentLink(['status' => 'archived']))]);

    $link = Paymongo::paymentLinks()->archive('plink_uSJXoxTBNqRrg35kj5w9dTVY');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PATCH'
        && $request->url() === 'https://api.paymongo.com/v1/payment_links/plink_uSJXoxTBNqRrg35kj5w9dTVY'
        && $request->data() === ['status' => 'archived']);

    expect($link->status)->toBe(PaymentLinkStatus::Archived);
});

it('unarchives a payment link through a status-only flat PATCH', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::paymentLink())]);

    $link = Paymongo::paymentLinks()->unarchive('plink_uSJXoxTBNqRrg35kj5w9dTVY');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PATCH'
        && $request->data() === ['status' => 'active']);

    expect($link->status)->toBe(PaymentLinkStatus::Active);
});

it('lists payment links mapping the flat items onto DTOs', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(payment_links_first_page())]);

    $page = Paymongo::paymentLinks()->list(['limit' => 2]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payment_links?limit=2'
        && $request->body() === '');

    expect($page)->toBeInstanceOf(CursorPage::class)
        ->and($page)->toHaveCount(2)
        ->and($page->hasMore)->toBeTrue()
        ->and($page->first())->toBeInstanceOf(PaymentLink::class)
        ->and($page->first()?->id)->toBe('plink_uSJXoxTBNqRrg35kj5w9dTVY')
        ->and($page->items[1]->id)->toBe('plink_SecondFlatLink2345678901')
        ->and($page->items[1]->status)->toBe(PaymentLinkStatus::Archived);
});

it('propagates the after cursor from the last payment link when paging', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->push(payment_links_first_page())
        ->push(payment_links_second_page());

    $page = Paymongo::paymentLinks()->list(['limit' => 2]);

    $next = $page->nextPage();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/payment_links?limit=2');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/payment_links?limit=2&after=plink_SecondFlatLink2345678901');

    Http::assertSentCount(2);

    expect($next)->toBeInstanceOf(CursorPage::class)
        ->and($next)->toHaveCount(1)
        ->and($next?->first()?->id)->toBe('plink_ThirdFlatLink3456789012')
        ->and($next?->hasMore)->toBeFalse()
        ->and($next?->nextPage())->toBeNull();
});

it('lists the payments of a payment link as standard triple resources', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::list([Fixtures::payment()]))]);

    $page = Paymongo::paymentLinks()->payments('plink_uSJXoxTBNqRrg35kj5w9dTVY', ['limit' => 10]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payment_links/plink_uSJXoxTBNqRrg35kj5w9dTVY/payments?limit=10'
        && $request->body() === '');

    expect($page)->toBeInstanceOf(CursorPage::class)
        ->and($page)->toHaveCount(1)
        ->and($page->first())->toBeInstanceOf(Payment::class)
        ->and($page->first()?->id)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi')
        ->and($page->first()?->status)->toBe(PaymentStatus::Paid);
});

it('refunds a payment link payment and maps the refund DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::refund(['amount' => 10000, 'reason' => 'others']), 201)]);

    $refund = Paymongo::paymentLinks()->refund('plink_uSJXoxTBNqRrg35kj5w9dTVY', [
        'payment_id' => 'pay_hvTn9EyxduZ9gV8WHhSGYqBi',
        'amount' => 100,
        'reason' => 'others',
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payment_links/plink_uSJXoxTBNqRrg35kj5w9dTVY/refunds'
        && $request->data() === ['payment_id' => 'pay_hvTn9EyxduZ9gV8WHhSGYqBi', 'amount' => 100, 'reason' => 'others']);

    expect($refund)->toBeInstanceOf(Refund::class)
        ->and($refund->id)->toBe('ref_9K2Wf3mLpQvXsTzYbNcVdGhJ')
        ->and($refund->amount)->toBe(10000)
        ->and($refund->reason)->toBe(RefundReason::Others)
        ->and($refund->paymentId)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi');
});
