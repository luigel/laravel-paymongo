<?php

use Luigel\Paymongo\Exceptions\NotFoundException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Checkout;

it('can create a checkout session', function () {
    $checkout = createCheckout();

    expect($checkout)->toBeInstanceOf(Checkout::class);
});

it('can not retrieve a checkout session with invalid id', function () {
    $this->expectException(NotFoundException::class);

    Paymongo::checkout()
        ->find('test');
});

it('can retrieve a checkout session by id', function () {
    $checkout = createCheckout();

    $retrieve = Paymongo::checkout()
        ->find($checkout->id);

    expect($checkout->id)->toBe($retrieve->id);
});

it('can expire a checkout session', function () {
    $checkout = createCheckout();

    $checkout = $checkout->expire();

    expect($checkout->status)->toBe('expired');
});
