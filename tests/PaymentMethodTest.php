<?php

use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\PaymentMethod;

it('can create payment method', function () {
    $paymentMethod = createPaymentMethod();

    expect($paymentMethod)
        ->toBeInstanceOf(PaymentMethod::class)
        ->type->toBe('payment_method')
        ->payment_method_type->toBe('card');
    expect($paymentMethod->details['last4'])
        ->toBe(getTestCardLast4());
    expect($paymentMethod->details['exp_month'])
        ->toBe(12);
    expect($paymentMethod->billing['address']['city'])
        ->toBe('Cebu City');
});

it('cannot create payment method with invalid_data', function () {
    $this->expectException(BadRequestException::class);

    Paymongo::paymentMethod()
        ->create([]);
});

it('can retrieve payment method', function () {
    $paymentMethodCreated = createPaymentMethod();

    $paymentMethod = Paymongo::paymentMethod()->find($paymentMethodCreated->id);

    $this->assertEquals($paymentMethodCreated, $paymentMethod);
});

it('can create paymaya payment method', function () {
    $paymentMethod = Paymongo::paymentMethod()
       ->create([
           'type' => 'paymaya',
           'billing' => [
               'address' => [
                   'line1' => 'Somewhere there',
                   'city' => 'Cebu City',
                   'state' => 'Cebu',
                   'country' => 'PH',
                   'postal_code' => '6000',
               ],
               'name' => 'Rigel Kent Carbonel',
               'email' => 'rigel20.kent@gmail.com',
               'phone' => '0935454875545',
           ],
       ]);

    expect($paymentMethod)
       ->toBeInstanceOf(PaymentMethod::class)
       ->type->toBe('payment_method')
       ->payment_method_type->toBe('paymaya');
});