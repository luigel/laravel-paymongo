<?php

declare(strict_types=1);

use Luigel\Paymongo\Enums\CheckoutSessionStatus;
use Luigel\Paymongo\Facades\Paymongo;

beforeEach(function (): void {
    if (!env('PAYMONGO_CONTRACT_TESTS')) {
        $this->markTestSkipped('Set PAYMONGO_CONTRACT_TESTS=1 to run the PayMongo contract tests.');
    }

    if (!str_starts_with((string) env('PAYMONGO_SECRET_KEY'), 'sk_test_')) {
        $this->markTestSkipped('Contract tests only run with a PayMongo test-mode secret key (sk_test_...).');
    }
});

it('creates and expires a checkout session', function (): void {
    $session = Paymongo::checkoutSessions()->create([
        'line_items' => [
            [
                'amount'   => 10000,
                'currency' => 'PHP',
                'name'     => 'Contract test item',
                'quantity' => 1,
            ],
        ],
        'payment_method_types' => ['card'],
        'success_url'          => 'https://example.com/success',
        'cancel_url'           => 'https://example.com/cancel',
        'description'          => 'laravel-paymongo contract test',
    ]);

    expect($session->id)->toStartWith('cs_')
        ->and($session->checkoutUrl)->not->toBeEmpty()
        ->and($session->status)->toBe(CheckoutSessionStatus::Active);

    $expired = Paymongo::checkoutSessions()->expire($session->id);

    expect($expired->id)->toBe($session->id)
        ->and($expired->status)->toBe(CheckoutSessionStatus::Expired);
});
