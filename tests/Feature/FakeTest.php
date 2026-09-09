<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\CheckoutSession;
use Luigel\Paymongo\Data\CustomerPaymentMethod;
use Luigel\Paymongo\Data\Link;
use Luigel\Paymongo\Data\PaymentIntent;
use Luigel\Paymongo\Data\Plan;
use Luigel\Paymongo\Data\Subscription;
use Luigel\Paymongo\Exceptions\ResourceNotFoundException;
use Luigel\Paymongo\Facades\Paymongo;
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
