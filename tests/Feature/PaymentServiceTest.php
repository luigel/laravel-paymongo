<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PaymentStatus;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorPage;

/**
 * A single-payment response body for the second page of a paginated listing.
 *
 * @return array<string, mixed>
 */
function payments_second_page(): array
{
    $payment = fixture_data('payment')['data'];
    $payment['id'] = 'pay_ThirdPagePaymentId12345678';

    return ['data' => [$payment], 'has_more' => false];
}

it('creates a payment by charging a source and maps the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment'))]);

    $payment = Paymongo::payments()->create([
        'amount' => 150050,
        'currency' => 'PHP',
        'source' => ['id' => 'src_TtJ9XGgkY7mfFMBkxRWj2sQo', 'type' => 'source'],
    ], idempotencyKey: 'order-1234');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payments'
        && $request->header('Idempotency-Key') === ['order-1234']
        && $request->data() === ['data' => ['attributes' => [
            'amount' => 150050,
            'currency' => 'PHP',
            'source' => ['id' => 'src_TtJ9XGgkY7mfFMBkxRWj2sQo', 'type' => 'source'],
        ]]]);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->id)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi')
        ->and($payment->amount)->toBe(150050);
});

it('retrieves a payment and maps the response onto the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment'))]);

    $payment = Paymongo::payments()->retrieve('pay_hvTn9EyxduZ9gV8WHhSGYqBi');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payments/pay_hvTn9EyxduZ9gV8WHhSGYqBi'
        && $request->body() === '');

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->id)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi')
        ->and($payment->type)->toBe('payment')
        ->and($payment->amount)->toBe(150050)
        ->and($payment->currency)->toBe(Currency::PHP)
        ->and($payment->status)->toBe(PaymentStatus::Paid)
        ->and($payment->fee)->toBe(5252)
        ->and($payment->netAmount)->toBe(144798)
        ->and($payment->statementDescriptor)->toBe('LUIGEL STORE')
        ->and($payment->source)->toBe(['id' => 'card_wjRvHkgtLHMAtaKuQoQGtiPT', 'type' => 'card'])
        ->and($payment->paymentIntentId)->toBe('pi_UWL2ZP2rBjMPS9UfnqAROSXg')
        ->and($payment->externalReferenceNumber)->toBeNull()
        ->and($payment->billing?->address?->city)->toBe('Taguig')
        ->and($payment->paidAt()?->getTimestamp())->toBe(1725840060)
        ->and($payment->money()?->format())->toBe('₱1,500.50');
});

it('lists payments passing the query parameters through', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payments_list'))]);

    $page = Paymongo::payments()->list(['limit' => 10, 'before' => 'pay_zzz']);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payments?limit=10&before=pay_zzz'
        && $request->body() === '');

    expect($page)->toBeInstanceOf(CursorPage::class)
        ->and($page)->toHaveCount(2)
        ->and($page->hasMore)->toBeTrue()
        ->and($page->first())->toBeInstanceOf(Payment::class)
        ->and($page->first()?->id)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi')
        ->and($page->items[1]->id)->toBe('pay_Mw7qLcJk2ZtR5yXbA8sVdN3e')
        ->and($page->items[1]->status)->toBe(PaymentStatus::Paid);
});

it('propagates the after cursor from the last item when fetching the next page', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->push(fixture_data('payments_list'))
        ->push(payments_second_page());

    $page = Paymongo::payments()->list(['limit' => 2]);

    expect($page)->toHaveCount(2)
        ->and($page->hasMore)->toBeTrue();

    $next = $page->nextPage();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/payments?limit=2');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/payments?limit=2&after=pay_Mw7qLcJk2ZtR5yXbA8sVdN3e');

    Http::assertSentCount(2);

    expect($next)->toBeInstanceOf(CursorPage::class)
        ->and($next)->toHaveCount(1)
        ->and($next?->first()?->id)->toBe('pay_ThirdPagePaymentId12345678')
        ->and($next?->hasMore)->toBeFalse()
        ->and($next?->nextPage())->toBeNull();
});

it('lazily yields every item across all pages', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->push(fixture_data('payments_list'))
        ->push(payments_second_page());

    $ids = Paymongo::payments()->list(['limit' => 2])
        ->lazy()
        ->map(fn (Payment $payment): string => $payment->id)
        ->all();

    expect($ids)->toBe([
        'pay_hvTn9EyxduZ9gV8WHhSGYqBi',
        'pay_Mw7qLcJk2ZtR5yXbA8sVdN3e',
        'pay_ThirdPagePaymentId12345678',
    ]);

    Http::assertSentCount(2);
});
