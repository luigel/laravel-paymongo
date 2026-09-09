<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Customer;
use Luigel\Paymongo\Data\CustomerPaymentMethod;
use Luigel\Paymongo\Enums\DefaultDevice;
use Luigel\Paymongo\Facades\Paymongo;

it('creates a customer normalizing the default device enum and maps the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('customer'))]);

    $customer = Paymongo::customers()->create([
        'first_name' => 'Juan',
        'last_name' => 'dela Cruz',
        'phone' => '+639171234567',
        'email' => 'juan@example.com',
        'default_device' => DefaultDevice::Phone,
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/customers'
            && $request->data() === ['data' => ['attributes' => [
                'first_name' => 'Juan',
                'last_name' => 'dela Cruz',
                'phone' => '+639171234567',
                'email' => 'juan@example.com',
                'default_device' => 'phone',
            ]]];
    });

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->id)->toBe('cus_hcjuejWKpU1YZi3sBDGGpx8M')
        ->and($customer->type)->toBe('customer')
        ->and($customer->firstName)->toBe('Juan')
        ->and($customer->lastName)->toBe('dela Cruz')
        ->and($customer->email)->toBe('juan@example.com')
        ->and($customer->phone)->toBe('+639171234567')
        ->and($customer->defaultDevice)->toBe(DefaultDevice::Phone)
        ->and($customer->defaultPaymentMethodId)->toBe('pm_ZzVPFGwGe31eR2vDcPuS9tsA')
        ->and($customer->livemode)->toBeFalse();
});

it('retrieves a customer', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('customer'))]);

    $customer = Paymongo::customers()->retrieve('cus_hcjuejWKpU1YZi3sBDGGpx8M');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v1/customers/cus_hcjuejWKpU1YZi3sBDGGpx8M'
            && $request->body() === '';
    });

    expect($customer->id)->toBe('cus_hcjuejWKpU1YZi3sBDGGpx8M');
});

it('updates a customer via PATCH with the envelope', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('customer'))]);

    $customer = Paymongo::customers()->update('cus_hcjuejWKpU1YZi3sBDGGpx8M', [
        'default_device' => 'email',
        'email' => 'juan.delacruz@example.com',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'PATCH'
            && $request->url() === 'https://api.paymongo.com/v1/customers/cus_hcjuejWKpU1YZi3sBDGGpx8M'
            && $request->data() === ['data' => ['attributes' => [
                'default_device' => 'email',
                'email' => 'juan.delacruz@example.com',
            ]]];
    });

    expect($customer)->toBeInstanceOf(Customer::class);
});

it('deletes a customer and returns true', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('customer'))]);

    $deleted = Paymongo::customers()->delete('cus_hcjuejWKpU1YZi3sBDGGpx8M');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://api.paymongo.com/v1/customers/cus_hcjuejWKpU1YZi3sBDGGpx8M'
            && $request->body() === '';
    });

    expect($deleted)->toBeTrue();
});

it('lists the payment methods of a customer as DTOs', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('customer_payment_methods_list'))]);

    $paymentMethods = Paymongo::customers()->paymentMethods('cus_hcjuejWKpU1YZi3sBDGGpx8M');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v1/customers/cus_hcjuejWKpU1YZi3sBDGGpx8M/payment_methods'
            && $request->body() === '';
    });

    expect($paymentMethods)->toBeArray()
        ->and($paymentMethods)->toHaveCount(2)
        ->and($paymentMethods[0])->toBeInstanceOf(CustomerPaymentMethod::class)
        ->and($paymentMethods[0]->id)->toBe('cpm_Qh8FF6cyzsWZUAeqvXCQzPu6')
        ->and($paymentMethods[0]->type)->toBe('customer_payment_method')
        ->and($paymentMethods[0]->paymentMethodId)->toBe('pm_ZzVPFGwGe31eR2vDcPuS9tsA')
        ->and($paymentMethods[0]->paymentMethodType)->toBe('card')
        ->and($paymentMethods[0]->sessionType)->toBe('on_session')
        ->and($paymentMethods[0]->details)->toBe([
            'last4' => '4345',
            'exp_month' => 12,
            'exp_year' => 2028,
            'brand' => 'visa',
        ])
        ->and($paymentMethods[0]->livemode)->toBeFalse()
        ->and($paymentMethods[1]->id)->toBe('cpm_Wk3RmNp7YtXzB2vC5sD8eFg4')
        ->and($paymentMethods[1]->paymentMethodType)->toBe('gcash')
        ->and($paymentMethods[1]->details)->toBeNull();
});

it('deletes a customer payment method and returns true', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => null])]);

    $deleted = Paymongo::customers()->deletePaymentMethod('cus_hcjuejWKpU1YZi3sBDGGpx8M', 'pm_ZzVPFGwGe31eR2vDcPuS9tsA');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://api.paymongo.com/v1/customers/cus_hcjuejWKpU1YZi3sBDGGpx8M/payment_methods/pm_ZzVPFGwGe31eR2vDcPuS9tsA'
            && $request->body() === '';
    });

    expect($deleted)->toBeTrue();
});
