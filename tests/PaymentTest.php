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
    $cardPayment = createCardPayment();

    $payment = Paymongo::payment()
        ->find($cardPayment->id);

    expect($cardPayment->id)->toBe($payment->id);
});

it('can create payment', function () {
    $cardPayment = createCardPayment();

    expect($cardPayment)
        ->toBeInstanceOf(Payment::class)
        ->amount->toBe(100.00)
        ->currency->toBe('PHP')
        ->statement_descriptor->toBe('LUIGEL STORE')
        ->status->toBe('paid');
});