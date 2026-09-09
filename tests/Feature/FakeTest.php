<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\CheckoutSession;
use Luigel\Paymongo\Data\CustomerPaymentMethod;
use Luigel\Paymongo\Data\Link;
use Luigel\Paymongo\Data\MpmQr;
use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Data\PaymentIntent;
use Luigel\Paymongo\Data\PaymentLink;
use Luigel\Paymongo\Data\Payout;
use Luigel\Paymongo\Data\PayoutSchedule;
use Luigel\Paymongo\Data\PayoutTransaction;
use Luigel\Paymongo\Data\Plan;
use Luigel\Paymongo\Data\QrExecution;
use Luigel\Paymongo\Data\StaticQr;
use Luigel\Paymongo\Data\Subscription;
use Luigel\Paymongo\Enums\PaymentLinkStatus;
use Luigel\Paymongo\Enums\QrMode;
use Luigel\Paymongo\Exceptions\ResourceNotFoundException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorTokenPage;
use Luigel\Paymongo\Testing\Fixtures;

it('fakes payment intent creation and echoes the request attributes', function () {
    Paymongo::fake();

    $intent = Paymongo::paymentIntents()->create([
        'amount' => 99999,
        'currency' => 'PHP',
        'description' => 'Faked order',
        'statement_descriptor' => 'FAKE STORE',
    ]);

    expect($intent)->toBeInstanceOf(PaymentIntent::class)
        ->and($intent->id)->toStartWith('pi_')
        ->and($intent->amount)->toBe(99999)
        ->and($intent->description)->toBe('Faked order')
        ->and($intent->statementDescriptor)->toBe('FAKE STORE');
});

it('fakes retrieval and echoes the requested id', function () {
    Paymongo::fake();

    $intent = Paymongo::paymentIntents()->retrieve('pi_custom_123');

    expect($intent->id)->toBe('pi_custom_123');
});

it('lets user stubs win over the default catch-all', function () {
    Paymongo::fake([
        'api.paymongo.com/v1/payment_intents*' => Http::response(
            Fixtures::paymentIntent(['id' => 'pi_stubbed', 'amount' => 12345])
        ),
    ]);

    $intent = Paymongo::paymentIntents()->create(['amount' => 99999, 'currency' => 'PHP']);

    expect($intent->id)->toBe('pi_stubbed')
        ->and($intent->amount)->toBe(12345);
});

it('records requests for assertSent', function () {
    Paymongo::fake();

    Paymongo::paymentIntents()->create(['amount' => 10000, 'currency' => 'PHP']);

    Paymongo::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/payment_intents'
            && $request->data() === ['data' => ['attributes' => ['amount' => 10000, 'currency' => 'PHP']]];
    });
});

it('passes assertNothingSent when no request was made', function () {
    Paymongo::fake();

    Paymongo::assertNothingSent();
});

it('interoperates with the Http facade assertions', function () {
    Paymongo::fake();

    Paymongo::paymentIntents()->retrieve('pi_custom_123');

    Http::assertSentCount(1);
});

it('fakes checkout session creation through the catch-all', function () {
    Paymongo::fake();

    $session = Paymongo::checkoutSessions()->create([
        'description' => 'Fake checkout',
        'reference_number' => 'REF-999',
        'success_url' => 'https://example.test/success',
    ]);

    expect($session)->toBeInstanceOf(CheckoutSession::class)
        ->and($session->id)->toStartWith('cs_')
        ->and($session->description)->toBe('Fake checkout')
        ->and($session->referenceNumber)->toBe('REF-999')
        ->and($session->successUrl)->toBe('https://example.test/success')
        ->and($session->checkoutUrl)->toStartWith('https://checkout.paymongo.com/');
});

it('routes subscription retrieval through the catch-all', function () {
    Paymongo::fake();

    $subscription = Paymongo::subscriptions()->retrieve('sub_fake_123');

    expect($subscription)->toBeInstanceOf(Subscription::class)
        ->and($subscription->id)->toBe('sub_fake_123')
        ->and($subscription->type)->toBe('subscription');
});

it('routes plan endpoints before the subscription wildcard', function () {
    Paymongo::fake();

    $plan = Paymongo::plans()->retrieve('plan_fake_123');

    expect($plan)->toBeInstanceOf(Plan::class)
        ->and($plan->id)->toBe('plan_fake_123')
        ->and($plan->type)->toBe('plan');
});

it('returns a matching list for link lookup by reference number', function () {
    Paymongo::fake();

    $link = Paymongo::links()->retrieveByReference('REF-777');

    expect($link)->toBeInstanceOf(Link::class)
        ->and($link->referenceNumber)->toBe('REF-777');
});

it('lists customer payment methods and fakes deleting one', function () {
    Paymongo::fake();

    $methods = Paymongo::customers()->paymentMethods('cus_fake_123');

    expect($methods)->toHaveCount(1)
        ->and($methods[0])->toBeInstanceOf(CustomerPaymentMethod::class)
        ->and($methods[0]->id)->toStartWith('cpm_');

    expect(Paymongo::customers()->deletePaymentMethod('cus_fake_123', 'cpm_fake_456'))->toBeTrue();
});

it('returns single-item pages for list endpoints', function () {
    Paymongo::fake();

    $page = Paymongo::payments()->list();

    expect($page->items)->toHaveCount(1)
        ->and($page->hasMore)->toBeFalse()
        ->and($page->items[0]->id)->toStartWith('pay_');
});

it('answers the subscription test cycle endpoint with an empty body', function () {
    Paymongo::fake();

    Paymongo::subscriptions()->triggerTestCycle('sub_fake_123');

    Paymongo::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/subscriptions/sub_fake_123/test_cycle';
    });
});

it('throws a resource not found error for unrouted paths under the base url', function () {
    Paymongo::fake();

    Paymongo::client()->get('/nonexistent');
})->throws(ResourceNotFoundException::class, 'No fake PayMongo route matches [GET /nonexistent].');

it('fakes MPM QR generation and execution with flat echoes', function () {
    Paymongo::fake();

    $qr = Paymongo::qrph()->generate([
        'nation' => 'ph',
        'mode' => 'p2p',
        'transaction_amount' => 7500,
    ]);

    expect($qr)->toBeInstanceOf(MpmQr::class)
        ->and($qr->id)->toStartWith('qr_')
        ->and($qr->mode)->toBe(QrMode::P2p)
        ->and($qr->transactionAmount)->toBe(7500);

    $execution = Paymongo::qrph()->execute([
        'qr_string' => '00020101021228_example',
        'amount' => 7500,
        'reference_number' => 'QR-FAKE-1',
    ]);

    expect($execution)->toBeInstanceOf(QrExecution::class)
        ->and($execution->id)->toStartWith('qrx_')
        ->and($execution->amount)->toBe(7500)
        ->and($execution->referenceNumber)->toBe('QR-FAKE-1');

    Paymongo::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v3/qr/mpm/generate'
            && ! array_key_exists('data', $request->data());
    });
});

it('routes v3 QR retrieval and expiry through the origin catch-all', function () {
    Paymongo::fake();

    $qr = Paymongo::qrph()->retrieve('qr_fake_123', qrString: true);

    expect($qr->id)->toBe('qr_fake_123');

    $expired = Paymongo::qrph()->expire('qr_fake_123');

    expect($expired->id)->toBe('qr_fake_123');
});

it('fakes static QR Ph generation with an enveloped echo', function () {
    Paymongo::fake();

    $code = Paymongo::qrph()->generateStatic([
        'kind' => 'instore',
        'mobile_number' => '+639998887766',
    ]);

    expect($code)->toBeInstanceOf(StaticQr::class)
        ->and($code->id)->toStartWith('qrph_')
        ->and($code->type)->toBe('code')
        ->and($code->mobileNumber)->toBe('+639998887766');
});

it('fakes payment link creation and update with flat echoes', function () {
    Paymongo::fake();

    $link = Paymongo::paymentLinks()->create([
        'amount' => 25000,
        'currency' => 'PHP',
        'description' => 'Faked payment link',
    ]);

    expect($link)->toBeInstanceOf(PaymentLink::class)
        ->and($link->id)->toStartWith('plink_')
        ->and($link->amount)->toBe(25000)
        ->and($link->description)->toBe('Faked payment link');

    $archived = Paymongo::paymentLinks()->archive('plink_fake_123');

    expect($archived->id)->toBe('plink_fake_123')
        ->and($archived->status)->toBe(PaymentLinkStatus::Archived);
});

it('lists payment links as a flat list and retrieves one by id', function () {
    Paymongo::fake();

    $page = Paymongo::paymentLinks()->list();

    expect($page->items)->toHaveCount(1)
        ->and($page->hasMore)->toBeFalse()
        ->and($page->items[0])->toBeInstanceOf(PaymentLink::class)
        ->and($page->items[0]->id)->toStartWith('plink_');

    expect(Paymongo::paymentLinks()->retrieve('plink_fake_456')->id)->toBe('plink_fake_456');
});

it('routes payment link payments and refunds', function () {
    Paymongo::fake();

    $payments = Paymongo::paymentLinks()->payments('plink_fake_123');

    expect($payments->items)->toHaveCount(1)
        ->and($payments->items[0])->toBeInstanceOf(Payment::class)
        ->and($payments->items[0]->id)->toStartWith('pay_');

    $refund = Paymongo::paymentLinks()->refund('plink_fake_123', ['reason' => 'others']);

    expect($refund)->toBe(['reason' => 'others']);
});

it('fakes payout listing, retrieval and transactions with token pagination', function () {
    Paymongo::fake();

    $page = Paymongo::payouts()->list();

    expect($page)->toBeInstanceOf(CursorTokenPage::class)
        ->and($page->items)->toHaveCount(1)
        ->and($page->items[0])->toBeInstanceOf(Payout::class)
        ->and($page->items[0]->id)->toStartWith('po_')
        ->and($page->nextCursor)->toBeNull()
        ->and($page->meta['total_records'] ?? null)->toBe(1);

    expect(Paymongo::payouts()->retrieve('po_fake_123')->id)->toBe('po_fake_123');

    $transactions = Paymongo::payouts()->transactions('po_fake_123');

    expect($transactions->items)->toHaveCount(1)
        ->and($transactions->items[0])->toBeInstanceOf(PayoutTransaction::class)
        ->and($transactions->items[0]->transactionType())->toBe('payment')
        ->and($transactions->nextCursor)->toBeNull();
});

it('fakes the payout schedule endpoint', function () {
    Paymongo::fake();

    $schedule = Paymongo::payouts()->schedule('org_fake_123');

    expect($schedule)->toBeInstanceOf(PayoutSchedule::class)
        ->and($schedule->scheduleType)->toBe('automatic')
        ->and($schedule->options)->toContain('automatic');
});
