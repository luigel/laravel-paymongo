<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Plan;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PlanInterval;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorPage;

it('creates a plan under /subscriptions/plans and maps the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('plan'))]);

    $plan = Paymongo::plans()->create([
        'name'           => 'Premium Monthly',
        'description'    => 'Premium tier billed monthly',
        'amount'         => 150050,
        'currency'       => 'PHP',
        'interval'       => PlanInterval::Monthly,
        'interval_count' => 1,
        'cycle_count'    => 12,
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/plans'
        && $request->data() === ['data' => ['attributes' => [
            'name'           => 'Premium Monthly',
            'description'    => 'Premium tier billed monthly',
            'amount'         => 150050,
            'currency'       => 'PHP',
            'interval'       => 'monthly',
            'interval_count' => 1,
            'cycle_count'    => 12,
        ]]]
        && $request->hasHeader('Idempotency-Key'));

    expect($plan)->toBeInstanceOf(Plan::class)
        ->and($plan->id)->toBe('plan_Ho5Fp9vJkTqW2xYzB3cD4eFg')
        ->and($plan->type)->toBe('plan')
        ->and($plan->amount)->toBe(150050)
        ->and($plan->currency)->toBe(Currency::PHP)
        ->and($plan->description)->toBe('Premium tier billed monthly')
        ->and($plan->interval)->toBe(PlanInterval::Monthly)
        ->and($plan->intervalCount)->toBe(1)
        ->and($plan->name)->toBe('Premium Monthly')
        ->and($plan->planType)->toBe('scheduled')
        ->and($plan->cycleCount)->toBe(12)
        ->and($plan->livemode)->toBeFalse()
        ->and($plan->money()?->format())->toBe('₱1,500.50');
});

it('retrieves a plan from /subscriptions/plans/{id}', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('plan'))]);

    $plan = Paymongo::plans()->retrieve('plan_Ho5Fp9vJkTqW2xYzB3cD4eFg');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/plans/plan_Ho5Fp9vJkTqW2xYzB3cD4eFg'
        && $request->body() === '');

    expect($plan->id)->toBe('plan_Ho5Fp9vJkTqW2xYzB3cD4eFg');
});

it('updates a plan via PATCH with the envelope', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('plan'))]);

    $plan = Paymongo::plans()->update('plan_Ho5Fp9vJkTqW2xYzB3cD4eFg', [
        'name' => 'Premium Monthly v2',
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PATCH'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/plans/plan_Ho5Fp9vJkTqW2xYzB3cD4eFg'
        && $request->data() === ['data' => ['attributes' => [
            'name' => 'Premium Monthly v2',
        ]]]);

    expect($plan)->toBeInstanceOf(Plan::class);
});

it('lists plans from /subscriptions/plans passing the query parameters through', function () {
    Http::fake(['api.paymongo.com/*' => Http::response([
        'data'     => [fixture_data('plan')['data']],
        'has_more' => false,
    ])]);

    $page = Paymongo::plans()->list(['limit' => 5]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/plans?limit=5'
        && $request->body() === '');

    expect($page)->toBeInstanceOf(CursorPage::class)
        ->and($page)->toHaveCount(1)
        ->and($page->hasMore)->toBeFalse()
        ->and($page->first())->toBeInstanceOf(Plan::class)
        ->and($page->first()?->id)->toBe('plan_Ho5Fp9vJkTqW2xYzB3cD4eFg')
        ->and($page->nextPage())->toBeNull();
});
