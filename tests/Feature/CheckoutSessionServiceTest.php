<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\CheckoutSession;
use Luigel\Paymongo\Data\LineItem;
use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Data\PaymentIntent;
use Luigel\Paymongo\Enums\CheckoutSessionStatus;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Enums\PaymentStatus;
use Luigel\Paymongo\Facades\Paymongo;

it('creates a checkout session and maps the response onto the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('checkout_session'))]);

    $session = Paymongo::checkoutSessions()->create([
        'line_items' => [
            ['amount' => 150050, 'currency' => 'PHP', 'name' => 'Leather Wallet', 'quantity' => 1],
        ],
        'payment_method_types' => ['card', 'gcash', 'paymaya'],
        'success_url' => 'https://example.com/success',
        'cancel_url' => 'https://example.com/cancel',
        'reference_number' => 'ORDER-10101',
        'description' => 'Order #10101 checkout',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/checkout_sessions'
            && $request->data() === ['data' => ['attributes' => [
                'line_items' => [
                    ['amount' => 150050, 'currency' => 'PHP', 'name' => 'Leather Wallet', 'quantity' => 1],
                ],
                'payment_method_types' => ['card', 'gcash', 'paymaya'],
                'success_url' => 'https://example.com/success',
                'cancel_url' => 'https://example.com/cancel',
                'reference_number' => 'ORDER-10101',
                'description' => 'Order #10101 checkout',
            ]]]
            && $request->hasHeader('Idempotency-Key');
    });

    expect($session)->toBeInstanceOf(CheckoutSession::class)
        ->and($session->id)->toBe('cs_iVf3gnCsp7EFjE9q2SemiH6z')
        ->and($session->type)->toBe('checkout_session')
        ->and($session->status)->toBe(CheckoutSessionStatus::Active)
        ->and($session->checkoutUrl)->toBe('https://checkout.paymongo.com/cs_iVf3gnCsp7EFjE9q2SemiH6z_client_UnCthings7')
        ->and($session->clientKey)->toBe('cs_iVf3gnCsp7EFjE9q2SemiH6z_client_UnCthings7')
        ->and($session->referenceNumber)->toBe('ORDER-10101')
        ->and($session->paymentMethodTypes)->toBe(['card', 'gcash', 'paymaya'])
        ->and($session->sendEmailReceipt)->toBeTrue()
        ->and($session->showDescription)->toBeTrue()
        ->and($session->showLineItems)->toBeTrue()
        ->and($session->description)->toBe('Order #10101 checkout')
        ->and($session->successUrl)->toBe('https://example.com/success')
        ->and($session->cancelUrl)->toBe('https://example.com/cancel')
        ->and($session->metadata)->toBe(['order_id' => '10101'])
        ->and($session->billing?->name)->toBe('Juan dela Cruz')
        ->and($session->billing?->address?->city)->toBe('Taguig');
});

it('maps the line items onto LineItem DTOs', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('checkout_session'))]);

    $session = Paymongo::checkoutSessions()->retrieve('cs_iVf3gnCsp7EFjE9q2SemiH6z');

    expect($session->lineItems)->toHaveCount(2)
        ->and($session->lineItems[0])->toBeInstanceOf(LineItem::class)
        ->and($session->lineItems[0]->amount)->toBe(150050)
        ->and($session->lineItems[0]->currency)->toBe(Currency::PHP)
        ->and($session->lineItems[0]->description)->toBe('A premium leather wallet')
        ->and($session->lineItems[0]->images)->toBe(['https://images.example.com/wallet.png'])
        ->and($session->lineItems[0]->name)->toBe('Leather Wallet')
        ->and($session->lineItems[0]->quantity)->toBe(1)
        ->and($session->lineItems[0]->money()?->format())->toBe('₱1,500.50')
        ->and($session->lineItems[1]->name)->toBe('Gift Wrap')
        ->and($session->lineItems[1]->description)->toBeNull()
        ->and($session->lineItems[1]->images)->toBe([])
        ->and($session->lineItems[1]->quantity)->toBe(2);
});

it('maps the nested payment intent and payments', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('checkout_session'))]);

    $session = Paymongo::checkoutSessions()->retrieve('cs_iVf3gnCsp7EFjE9q2SemiH6z');

    expect($session->paymentIntent)->toBeInstanceOf(PaymentIntent::class)
        ->and($session->paymentIntent?->id)->toBe('pi_UWL2ZP2rBjMPS9UfnqAROSXg')
        ->and($session->paymentIntent?->type)->toBe('payment_intent')
        ->and($session->paymentIntent?->amount)->toBe(200050)
        ->and($session->paymentIntent?->status)->toBe(PaymentIntentStatus::Succeeded)
        ->and($session->payments)->toHaveCount(1)
        ->and($session->payments[0])->toBeInstanceOf(Payment::class)
        ->and($session->payments[0]->id)->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi')
        ->and($session->payments[0]->status)->toBe(PaymentStatus::Paid)
        ->and($session->payments[0]->amount)->toBe(200050);
});

it('sends an explicit idempotency key when creating', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('checkout_session'))]);

    Paymongo::checkoutSessions()->create(['payment_method_types' => ['card']], 'cs-idem-123');

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Idempotency-Key', 'cs-idem-123');
    });
});

it('retrieves a checkout session', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('checkout_session'))]);

    $session = Paymongo::checkoutSessions()->retrieve('cs_iVf3gnCsp7EFjE9q2SemiH6z');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v1/checkout_sessions/cs_iVf3gnCsp7EFjE9q2SemiH6z'
            && $request->body() === '';
    });

    expect($session->id)->toBe('cs_iVf3gnCsp7EFjE9q2SemiH6z');
});

it('expires a checkout session with an empty POST body', function () {
    $expired = fixture_data('checkout_session');
    $expired['data']['attributes']['status'] = 'expired';

    Http::fake(['api.paymongo.com/*' => Http::response($expired)]);

    $session = Paymongo::checkoutSessions()->expire('cs_iVf3gnCsp7EFjE9q2SemiH6z');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/checkout_sessions/cs_iVf3gnCsp7EFjE9q2SemiH6z/expire'
            && $request->body() === '';
    });

    expect($session)->toBeInstanceOf(CheckoutSession::class)
        ->and($session->status)->toBe(CheckoutSessionStatus::Expired);
});
