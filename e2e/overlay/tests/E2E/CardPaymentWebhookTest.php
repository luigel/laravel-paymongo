<?php

declare(strict_types=1);

use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Enums\RefundReason;
use Luigel\Paymongo\Facades\Paymongo;

/**
 * The full round trip: charge a sandbox card through the real PayMongo API,
 * then wait for PayMongo to POST the resulting events back through the public
 * tunnel into the running `php artisan serve` process.
 *
 * The two tests share state and run in file order; the refund test skips when
 * the charge did not produce a payment.
 */
$state = new class
{
    public ?string $intentId = null;

    public ?string $paymentId = null;

    public ?int $amount = null;
};

beforeEach(function () {
    if ($reason = e2eSkipReason()) {
        $this->markTestSkipped($reason);
    }

    if ($reason = e2eWebhookSkipReason()) {
        $this->markTestSkipped($reason);
    }

    truncateWebhookDeliveries();
});

it('charges a sandbox card and receives the payment.paid webhook', function () use ($state) {
    $intent = Paymongo::paymentIntents()->create([
        'amount' => 10000,
        'currency' => 'PHP',
        'payment_method_allowed' => ['card'],
        'payment_method_options' => [
            'card' => ['request_three_d_secure' => 'automatic'],
        ],
        'description' => 'laravel-paymongo e2e',
    ]);

    expect($intent->id)->toStartWith('pi_')
        ->and($intent->status)->toBe(PaymentIntentStatus::AwaitingPaymentMethod);

    $method = Paymongo::paymentMethods()->create([
        'type' => 'card',
        'details' => [
            // PayMongo sandbox card that succeeds without a 3DS challenge.
            'card_number' => '4343434343434345',
            'exp_month' => 12,
            'exp_year' => 34,
            'cvc' => '123',
        ],
        'billing' => [
            'name' => 'Juan dela Cruz',
            'email' => 'juan.delacruz@example.com',
            'phone' => '+639171234567',
        ],
    ]);

    expect($method->id)->toStartWith('pm_');

    $attached = Paymongo::paymentIntents()->attach(
        $intent->id,
        $method->id,
        returnUrl: rtrim((string) config('app.url'), '/').'/e2e/return',
    );

    expect($attached->status)->toBe(PaymentIntentStatus::Succeeded)
        ->and($attached->payments)->not->toBeEmpty();

    $payment = $attached->payments[0];

    $state->intentId = $intent->id;
    $state->paymentId = $payment->id;
    $state->amount = $payment->amount;

    $delivery = waitForDelivery(
        fn (object $row): bool => $row->event_type === 'payment.paid'
            && str_contains((string) $row->payload, $intent->id),
        timeoutSeconds: 60,
    );

    if ($delivery === null) {
        $this->fail("No payment.paid webhook for {$intent->id} arrived within 60s. Check that the tunnel is up and that PAYMONGO_WEBHOOK_SECRET belongs to a webhook registered at ".config('app.url').'/paymongo/webhook.');
    }

    expect($delivery->resource_type)->toBe('payment')
        ->and($delivery->resource_id)->toBe($payment->id)
        ->and($delivery->event_id)->toStartWith('evt_');
});

it('refunds the payment and receives the payment.refunded webhook', function () use ($state) {
    if ($state->paymentId === null) {
        $this->markTestSkipped('No payment id: the card charge test did not complete.');
    }

    $refund = Paymongo::refunds()->create([
        'amount' => $state->amount ?? 10000,
        'payment_id' => $state->paymentId,
        // NOTE: PayMongo also accepts `requested_by_customer`, but the package's
        // RefundReason enum only models duplicate|fraudulent|others, so that
        // value would round-trip as a null $refund->reason.
        'reason' => RefundReason::Others,
        'notes' => 'laravel-paymongo e2e',
    ]);

    expect($refund->id)->toStartWith('ref_')
        ->and($refund->paymentId)->toBe($state->paymentId);

    $delivery = waitForDelivery(
        fn (object $row): bool => $row->event_type === 'payment.refunded'
            && str_contains((string) $row->payload, (string) $state->paymentId),
        timeoutSeconds: 90,
    );

    if ($delivery === null) {
        $this->markTestIncomplete("No payment.refunded webhook for {$state->paymentId} arrived within 90s. Sandbox refund events are frequently delayed; the refund {$refund->id} itself was created successfully.");
    }

    expect($delivery->resource_type)->toBe('payment')
        ->and($delivery->resource_id)->toBe($state->paymentId);
});
