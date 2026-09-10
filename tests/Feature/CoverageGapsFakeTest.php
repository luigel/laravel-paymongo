<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Luigel\Paymongo\Data\Link;
use Luigel\Paymongo\Data\PaymentIntent;
use Luigel\Paymongo\Data\Plan;
use Luigel\Paymongo\Exceptions\ResourceNotFoundException;
use Luigel\Paymongo\Facades\Paymongo;

it('fakes plan updates and echoes the patched attributes onto the requested id', function () {
    Paymongo::fake();

    $plan = Paymongo::plans()->update('plan_fake_123', [
        'name'   => 'Premium Yearly',
        'amount' => 1500000,
    ]);

    Paymongo::assertSent(fn (Request $request): bool => $request->method() === 'PATCH'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/plans/plan_fake_123'
        && $request->data() === ['data' => ['attributes' => ['name' => 'Premium Yearly', 'amount' => 1500000]]]);

    expect($plan)->toBeInstanceOf(Plan::class)
        ->and($plan->id)->toBe('plan_fake_123')
        ->and($plan->type)->toBe('plan')
        ->and($plan->name)->toBe('Premium Yearly')
        ->and($plan->amount)->toBe(1500000);
});

it('throws a resource not found error for unrouted v3 paths under the origin', function () {
    Paymongo::fake();

    Paymongo::client()->get('https://api.paymongo.com/v3/disputes');
})->throws(ResourceNotFoundException::class, 'No fake PayMongo route matches [GET /disputes].');

it('routes faked requests through a base URL that carries a path prefix', function () {
    // Set before the manager is first resolved so the client is built with
    // an origin (`https://proxy.example.test/paymongo`) that has a path.
    config()->set('paymongo.base_url', 'https://proxy.example.test/paymongo/v1');

    Paymongo::fake();

    $intent = Paymongo::paymentIntents()->retrieve('pi_proxied_123');

    Paymongo::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://proxy.example.test/paymongo/v1/payment_intents/pi_proxied_123');

    expect(Paymongo::client()->config()->origin())->toBe('https://proxy.example.test/paymongo')
        ->and($intent)->toBeInstanceOf(PaymentIntent::class)
        ->and($intent->id)->toBe('pi_proxied_123')
        ->and($intent->type)->toBe('payment_intent');
});

it('returns the default link fixture for a link listing without a query string', function () {
    Paymongo::fake();

    $page = Paymongo::links()->list();

    Paymongo::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/links');

    expect($page->items)->toHaveCount(1)
        ->and($page->hasMore)->toBeFalse()
        ->and($page->items[0])->toBeInstanceOf(Link::class)
        ->and($page->items[0]->id)->toBe('link_NYWZmp6b6emCHo9uWmrBiJTx')
        ->and($page->items[0]->referenceNumber)->toBe('JCUV9NF');
});
