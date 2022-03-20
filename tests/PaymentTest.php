<?php

use Illuminate\Support\Collection;
use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Exceptions\NotFoundException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Payment;

it('can get all the payments', function () {
    $payments = Paymongo::payment()->all();

    expect($payments)->toBeInstanceOf(Collection::class);
});

it('can not retrieve a payment with invalid id', function () {
    $this->expectException(NotFoundException::class);

    Paymongo::payment()
        ->find('test');
});

it('can retrieve a payment', function () {
    $token = createToken();

    $createdPayment = createPayment($token);

    $payment = Paymongo::payment()
        ->find($createdPayment->id);

    expect($payment->id)->toBe($createdPayment->id);
});

it('cannot create payment when token is not valid', function () {
    $this->expectException(BadRequestException::class);

    $token = Paymongo::token()->create([
        'number' => '5100000000000198',
        'exp_month' => 12,
        'exp_year' => 25,
        'cvc' => '123',
        'billing' => [
            'address' => [
                'line1' => 'Test Address',
                'city' => 'Cebu City',
                'postal_code' => '6000',
                'country' => 'PH',
            ],
            'name' => 'Rigel Kent Carbonel',
            'email' => 'rigel20.kent@gmail.com',
            'phone' => '928392893',
        ],
    ]);

    createPayment($token);
});

it('cannot create payment when token is used more than once', function () {
    $this->expectException(BadRequestException::class);

    $token = createToken();
    $payment = createPayment($token);

    expect($payment)
        ->amount->toBe(100.00)
        ->currency->toBe('PHP')
        ->statement_descriptor->toBe('Test Paymongo')
        ->status->toBe('paid');

    createPayment($token);
});

it('can create payment', function () {
    $token = createToken();

    $payment = createPayment($token);

    expect($payment)
        ->toBeInstanceOf(Payment::class)
        ->amount->toBe(100.00)
        ->currency->toBe('PHP')
        ->statement_descriptor->toBe('Test Paymongo')
        ->status->toBe('paid');
});
