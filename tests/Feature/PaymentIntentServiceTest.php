<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\PaymentIntent;
use Luigel\Paymongo\Enums\CaptureType;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Exceptions\AuthenticationException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Services\PaymentIntentService;

it('creates a payment intent and maps the response onto the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    $intent = Paymongo::paymentIntents()->create([
        'amount'                 => 150050,
        'currency'               => 'PHP',
        'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
        'capture_type'           => 'automatic',
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payment_intents'
        && $request->data() === ['data' => ['attributes' => [
            'amount'                 => 150050,
            'currency'               => 'PHP',
            'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
            'capture_type'           => 'automatic',
        ]]]
        && $request->hasHeader('Idempotency-Key'));

    expect($intent)->toBeInstanceOf(PaymentIntent::class)
        ->and($intent->id)->toBe('pi_UWL2ZP2rBjMPS9UfnqAROSXg')
        ->and($intent->type)->toBe('payment_intent')
        ->and($intent->amount)->toBe(150050)
        ->and($intent->currency)->toBe(Currency::PHP)
        ->and($intent->status)->toBe(PaymentIntentStatus::AwaitingPaymentMethod)
        ->and($intent->captureType)->toBe(CaptureType::Automatic)
        ->and($intent->clientKey)->toBe('pi_UWL2ZP2rBjMPS9UfnqAROSXg_client_hVvMV6nHFvpaXV2EYVMTLNSZ')
        ->and($intent->description)->toBe('Order #10101')
        ->and($intent->statementDescriptor)->toBe('LUIGEL STORE')
        ->and($intent->livemode)->toBeFalse()
        ->and($intent->paymentMethodAllowed)->toBe(['card', 'gcash', 'paymaya'])
        ->and($intent->paymentMethodOptions)->toBe(['card' => ['request_three_d_secure' => 'any']])
        ->and($intent->payments)->toBe([])
        ->and($intent->nextAction)->toBeNull()
        ->and($intent->lastPaymentError)->toBeNull()
        ->and($intent->setupFutureUsage)->toBeNull()
        ->and($intent->metadata)->toBe(['order_id' => '10101'])
        ->and($intent->money()?->toDecimal())->toBe('1500.50');
});

it('sends an explicit idempotency key when creating', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    Paymongo::paymentIntents()->create(['amount' => 150050, 'currency' => 'PHP'], 'pi-idem-123');

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('Idempotency-Key', 'pi-idem-123'));
});

it('retrieves a payment intent', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    $intent = Paymongo::paymentIntents()->retrieve('pi_UWL2ZP2rBjMPS9UfnqAROSXg');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payment_intents/pi_UWL2ZP2rBjMPS9UfnqAROSXg'
        && $request->body() === '');

    expect($intent->id)->toBe('pi_UWL2ZP2rBjMPS9UfnqAROSXg');
});

it('retrieves using the client key with public-key authentication', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    $clientKey = 'pi_UWL2ZP2rBjMPS9UfnqAROSXg_client_hVvMV6nHFvpaXV2EYVMTLNSZ';
    $intent = Paymongo::paymentIntents()->retrieveUsingClientKey('pi_UWL2ZP2rBjMPS9UfnqAROSXg', $clientKey);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payment_intents/pi_UWL2ZP2rBjMPS9UfnqAROSXg?client_key='.$clientKey
        && $request->hasHeader('Authorization', 'Basic '.base64_encode('pk_test_fake:')));

    expect($intent)->toBeInstanceOf(PaymentIntent::class);
});

it('does not switch the manager client to the public key permanently', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    Paymongo::paymentIntents()->retrieveUsingClientKey('pi_UWL2ZP2rBjMPS9UfnqAROSXg', 'ck_test');
    Paymongo::paymentIntents()->retrieve('pi_UWL2ZP2rBjMPS9UfnqAROSXg');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/payment_intents/pi_UWL2ZP2rBjMPS9UfnqAROSXg'
        && $request->hasHeader('Authorization', 'Basic '.base64_encode('sk_test_fake:')));
});

it('throws a clear authentication exception when no public key is configured', function () {
    config()->set('paymongo.public_key');

    try {
        Paymongo::paymentIntents()->retrieveUsingClientKey('pi_123', 'ck_test');
        $this->fail('AuthenticationException was not thrown.');
    } catch (AuthenticationException $exception) {
        expect($exception->getMessage())->toContain('public key')
            ->and($exception->getMessage())->toContain('PAYMONGO_PUBLIC_KEY');
    }

    Http::assertNothingSent();
});

it('attaches a payment method with only the payment method id', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    Paymongo::paymentIntents()->attach('pi_UWL2ZP2rBjMPS9UfnqAROSXg', 'pm_ZzVPFGwGe31eR2vDcPuS9tsA');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payment_intents/pi_UWL2ZP2rBjMPS9UfnqAROSXg/attach'
        && $request->data() === ['data' => ['attributes' => [
            'payment_method' => 'pm_ZzVPFGwGe31eR2vDcPuS9tsA',
        ]]]);
});

it('attaches with a return url and client key when given', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    Paymongo::paymentIntents()->attach(
        'pi_UWL2ZP2rBjMPS9UfnqAROSXg',
        'pm_ZzVPFGwGe31eR2vDcPuS9tsA',
        'https://example.com/return',
        'ck_test_123',
    );

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payment_intents/pi_UWL2ZP2rBjMPS9UfnqAROSXg/attach'
        && $request->data() === ['data' => ['attributes' => [
            'payment_method' => 'pm_ZzVPFGwGe31eR2vDcPuS9tsA',
            'return_url'     => 'https://example.com/return',
            'client_key'     => 'ck_test_123',
        ]]]);
});

it('captures a partial amount with the amount in the body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    Paymongo::paymentIntents()->capture('pi_UWL2ZP2rBjMPS9UfnqAROSXg', 5000);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payment_intents/pi_UWL2ZP2rBjMPS9UfnqAROSXg/capture'
        && $request->data() === ['data' => ['attributes' => ['amount' => 5000]]]);
});

it('captures fully with no request body when the amount is omitted', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    Paymongo::paymentIntents()->capture('pi_UWL2ZP2rBjMPS9UfnqAROSXg');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payment_intents/pi_UWL2ZP2rBjMPS9UfnqAROSXg/capture'
        && $request->body() === '');
});

it('cancels a payment intent with no request body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_intent'))]);

    Paymongo::paymentIntents()->cancel('pi_UWL2ZP2rBjMPS9UfnqAROSXg');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payment_intents/pi_UWL2ZP2rBjMPS9UfnqAROSXg/cancel'
        && $request->body() === '');
});

it('memoizes the service on the manager and rebuilds it after withSecretKey', function () {
    $manager = app('paymongo');

    expect(Paymongo::paymentIntents())->toBeInstanceOf(PaymentIntentService::class)
        ->and(Paymongo::paymentIntents())->toBe($manager->paymentIntents());

    $other = $manager->withSecretKey('sk_test_other');

    expect($other->paymentIntents())->not->toBe($manager->paymentIntents());
});
