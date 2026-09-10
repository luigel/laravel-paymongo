<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\PaymentMethod;
use Luigel\Paymongo\Data\Shared\Billing;
use Luigel\Paymongo\Enums\PaymentMethodType;
use Luigel\Paymongo\Facades\Paymongo;

it('creates a payment method and maps the response onto the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_method'))]);

    $method = Paymongo::paymentMethods()->create([
        'type'    => 'card',
        'details' => [
            'card_number' => '4343434343434345',
            'exp_month'   => 12,
            'exp_year'    => 2030,
            'cvc'         => '123',
        ],
        'billing' => ['name' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@example.com'],
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/payment_methods'
        && $request->data() === ['data' => ['attributes' => [
            'type'    => 'card',
            'details' => [
                'card_number' => '4343434343434345',
                'exp_month'   => 12,
                'exp_year'    => 2030,
                'cvc'         => '123',
            ],
            'billing' => ['name' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@example.com'],
        ]]]);

    expect($method)->toBeInstanceOf(PaymentMethod::class)
        ->and($method->id)->toBe('pm_ZzVPFGwGe31eR2vDcPuS9tsA')
        ->and($method->type)->toBe('payment_method')
        ->and($method->methodType)->toBe(PaymentMethodType::Card)
        ->and($method->livemode)->toBeFalse()
        ->and($method->details)->toBe(['last4' => '4345', 'exp_month' => 12, 'exp_year' => 2030])
        ->and($method->metadata)->toBeNull()
        ->and($method->billing)->toBeInstanceOf(Billing::class)
        ->and($method->billing?->name)->toBe('Juan Dela Cruz')
        ->and($method->billing?->email)->toBe('juan.delacruz@example.com')
        ->and($method->billing?->phone)->toBe('+639171234567')
        ->and($method->billing?->address?->line1)->toBe('212 Sesame St.')
        ->and($method->billing?->address?->line2)->toBe('Apt 4B')
        ->and($method->billing?->address?->city)->toBe('Taguig')
        ->and($method->billing?->address?->state)->toBe('Metro Manila')
        ->and($method->billing?->address?->postalCode)->toBe('1630')
        ->and($method->billing?->address?->country)->toBe('PH');
});

it('retrieves a payment method', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('payment_method'))]);

    $method = Paymongo::paymentMethods()->retrieve('pm_ZzVPFGwGe31eR2vDcPuS9tsA');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payment_methods/pm_ZzVPFGwGe31eR2vDcPuS9tsA'
        && $request->body() === '');

    expect($method->id)->toBe('pm_ZzVPFGwGe31eR2vDcPuS9tsA')
        ->and($method->methodType)->toBe(PaymentMethodType::Card);
});
