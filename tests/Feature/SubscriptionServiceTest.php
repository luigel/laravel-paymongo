<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Plan;
use Luigel\Paymongo\Data\Subscription;
use Luigel\Paymongo\Enums\CancellationReason;
use Luigel\Paymongo\Enums\PlanInterval;
use Luigel\Paymongo\Enums\SubscriptionStatus;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorPage;

/**
 * The subscription fixture, mutated into a cancelled state.
 *
 * @return array<string, mixed>
 */
function cancelled_subscription(): array
{
    $subscription = fixture_data('subscription');
    $subscription['data']['attributes']['status'] = 'cancelled';
    $subscription['data']['attributes']['cancellation_reason'] = 'unused';
    $subscription['data']['attributes']['cancelled_at'] = 1726099200;

    return $subscription;
}

it('creates a subscription from a customer and plan id and maps the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('subscription'))]);

    $subscription = Paymongo::subscriptions()->create('cus_hcjuejWKpU1YZi3sBDGGpx8M', 'plan_Ho5Fp9vJkTqW2xYzB3cD4eFg');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions'
        && $request->data() === ['data' => ['attributes' => [
            'customer_id' => 'cus_hcjuejWKpU1YZi3sBDGGpx8M',
            'plan_id'     => 'plan_Ho5Fp9vJkTqW2xYzB3cD4eFg',
        ]]]
        && $request->hasHeader('Idempotency-Key'));

    expect($subscription)->toBeInstanceOf(Subscription::class)
        ->and($subscription->id)->toBe('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh')
        ->and($subscription->type)->toBe('subscription')
        ->and($subscription->customerId)->toBe('cus_hcjuejWKpU1YZi3sBDGGpx8M')
        ->and($subscription->planId)->toBe('plan_Ho5Fp9vJkTqW2xYzB3cD4eFg')
        ->and($subscription->paymentMethodId)->toBe('pm_ZzVPFGwGe31eR2vDcPuS9tsA')
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->anchorDate?->getTimestamp())->toBe(1725840000)
        ->and($subscription->nextBillingSchedule?->getTimestamp())->toBe(1728432000)
        ->and($subscription->cancelledAt)->toBeNull()
        ->and($subscription->cancellationReason)->toBeNull()
        ->and($subscription->latestInvoice)->toBe(['id' => 'inv_Uq7WnXp2YtRzB4vC6sD9eFg3', 'status' => 'paid'])
        ->and($subscription->setupIntent)->toBeNull()
        ->and($subscription->livemode)->toBeFalse();
});

it('maps the nested plan onto a Plan DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('subscription'))]);

    $subscription = Paymongo::subscriptions()->retrieve('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh');

    expect($subscription->plan)->toBeInstanceOf(Plan::class)
        ->and($subscription->plan?->id)->toBe('plan_Ho5Fp9vJkTqW2xYzB3cD4eFg')
        ->and($subscription->plan?->type)->toBe('plan')
        ->and($subscription->plan?->name)->toBe('Premium Monthly')
        ->and($subscription->plan?->interval)->toBe(PlanInterval::Monthly)
        ->and($subscription->plan?->amount)->toBe(150050)
        ->and($subscription->plan?->money()?->format())->toBe('₱1,500.50');
});

it('retrieves a subscription', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('subscription'))]);

    $subscription = Paymongo::subscriptions()->retrieve('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh'
        && $request->body() === '');

    expect($subscription->id)->toBe('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh');
});

it('lists subscriptions passing the query parameters through', function () {
    Http::fake(['api.paymongo.com/*' => Http::response([
        'data'     => [fixture_data('subscription')['data']],
        'has_more' => false,
    ])]);

    $page = Paymongo::subscriptions()->list(['limit' => 5, 'before' => 'sub_zzz']);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions?limit=5&before=sub_zzz'
        && $request->body() === '');

    expect($page)->toBeInstanceOf(CursorPage::class)
        ->and($page)->toHaveCount(1)
        ->and($page->hasMore)->toBeFalse()
        ->and($page->first())->toBeInstanceOf(Subscription::class)
        ->and($page->first()?->id)->toBe('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh');
});

it('cancels a subscription with an enum reason and maps the cancelled state', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(cancelled_subscription())]);

    $subscription = Paymongo::subscriptions()->cancel('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh', CancellationReason::Unused);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh/cancel'
        && $request->data() === ['data' => ['attributes' => [
            'cancellation_reason' => 'unused',
        ]]]);

    expect($subscription->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($subscription->cancellationReason)->toBe(CancellationReason::Unused)
        ->and($subscription->cancelledAt?->getTimestamp())->toBe(1726099200);
});

it('cancels a subscription with a plain string reason', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(cancelled_subscription())]);

    Paymongo::subscriptions()->cancel('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh', 'too_expensive');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh/cancel'
        && $request->data() === ['data' => ['attributes' => [
            'cancellation_reason' => 'too_expensive',
        ]]]);
});

it('changes the plan via PUT with the plan id in the body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('subscription'))]);

    $subscription = Paymongo::subscriptions()->changePlan('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh', 'plan_Yr8SnWq3ZuTxC5vD7sE2fGh6');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh/plan'
        && $request->data() === ['data' => ['attributes' => [
            'plan_id' => 'plan_Yr8SnWq3ZuTxC5vD7sE2fGh6',
        ]]]);

    expect($subscription)->toBeInstanceOf(Subscription::class);
});

it('changes the payment method via PUT with only the payment method id', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('subscription'))]);

    Paymongo::subscriptions()->changePaymentMethod('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh', 'pm_Bq6TnVr9ZuWxC3yD7sE2fGh5');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh/payment_method'
        && $request->data() === ['data' => ['attributes' => [
            'payment_method_id' => 'pm_Bq6TnVr9ZuWxC3yD7sE2fGh5',
        ]]]);
});

it('changes the payment method including the redirect url when given', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('subscription'))]);

    Paymongo::subscriptions()->changePaymentMethod(
        'sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh',
        'pm_Bq6TnVr9ZuWxC3yD7sE2fGh5',
        'https://example.com/billing',
    );

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh/payment_method'
        && $request->data() === ['data' => ['attributes' => [
            'payment_method_id' => 'pm_Bq6TnVr9ZuWxC3yD7sE2fGh5',
            'redirect_url'      => 'https://example.com/billing',
        ]]]);
});

it('triggers a test cycle with an empty POST body and returns nothing', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('subscription'))]);

    Paymongo::subscriptions()->triggerTestCycle('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/subscriptions/sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh/test_cycle'
        && $request->body() === '');

    Http::assertSentCount(1);

    $reflection = new ReflectionMethod(Paymongo::subscriptions(), 'triggerTestCycle');

    expect((string) $reflection->getReturnType())->toBe('void');
});
