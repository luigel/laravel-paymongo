<?php

declare(strict_types=1);

use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Facades\Paymongo;

beforeEach(function (): void {
    if (! env('PAYMONGO_CONTRACT_TESTS')) {
        $this->markTestSkipped('Set PAYMONGO_CONTRACT_TESTS=1 to run the PayMongo contract tests.');
    }

    if (! str_starts_with((string) env('PAYMONGO_SECRET_KEY'), 'sk_test_')) {
        $this->markTestSkipped('Contract tests only run with a PayMongo test-mode secret key (sk_test_...).');
    }
});

it('creates a minimal payment intent that awaits a payment method', function (): void {
    $intent = Paymongo::paymentIntents()->create([
        'amount' => 10000,
        'currency' => 'PHP',
        'payment_method_allowed' => ['card'],
        'description' => 'laravel-paymongo contract test',
    ]);

    expect($intent->id)->toStartWith('pi_')
        ->and($intent->status)->toBe(PaymentIntentStatus::AwaitingPaymentMethod)
        ->and($intent->amount)->toBe(10000)
        ->and($intent->livemode)->toBeFalse()
        ->and($intent->clientKey)->not->toBeNull();
});
