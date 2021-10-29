<?php

use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Exceptions\NotFoundException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\PaymentIntent;

it('can create payment intent', function () {
    $paymentIntent = createPaymentIntent();

    expect($paymentIntent)
        ->toBeInstanceOf(PaymentIntent::class)
        ->amount->toBe(100.00)
        ->status->toBe('awaiting_payment_method')
        ->statement_descriptor->toBe('LUIGEL STORE')
        ->type->toBe('payment_intent');
});

it('cannot create payment intent when data is invalid', function () {
    $this->expectException(BadRequestException::class);

    Paymongo::paymentIntent()->create([]);
});

it('can cancel payment intent', function () {
    $paymentIntent = createPaymentIntent();

    $cancelledPaymentIntent = $paymentIntent->cancel();

    expect($cancelledPaymentIntent)
        ->status->toBe('cancelled')
        ->id->toBe($paymentIntent->id);
});

it('can attach payment method to payment intent', function () {
    $paymentIntent = createPaymentIntent();

    $paymentMethod = Paymongo::paymentMethod()
        ->create([
            'type' => 'card',
            'details' => [
                'card_number' => getTestCardWithout3dSecure(),
                'exp_month' => 12,
                'exp_year' => 25,
                'cvc' => '123',
            ],
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

    $attachedPaymentIntent = $paymentIntent->attach($paymentMethod->id);

    expect($attachedPaymentIntent)
        ->id->toBe($paymentIntent->id)
        ->status->toBe('succeeded');
});

it('cannot attach payment intent with invalid data', function () {
    $this->expectException(NotFoundException::class);

    $paymentIntent = createPaymentIntent();

    $paymentIntent->attach('test');
});

it('can retrieve payment intent', function () {
    $paymentIntent = createPaymentIntent();

    $retrievedPaymentIntent = Paymongo::paymentIntent()->find($paymentIntent->id);

    $this->assertEquals($paymentIntent, $retrievedPaymentIntent);
});

it('can attach paymaya payment method to payment intent', function () {
    $paymentIntent = Paymongo::paymentIntent()
        ->create([
            'amount' => 100,
            'payment_method_allowed' => [
                'paymaya', 'card',
            ],
            'payment_method_options' => [
                'card' => [
                    'request_three_d_secure' => 'automatic',
                ],
            ],
            'description' => 'This is a test payment intent',
            'statement_descriptor' => 'LUIGEL STORE',
            'currency' => 'PHP',
        ]);

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

    $attachedPaymentIntent = $paymentIntent->attach($paymentMethod->id, 'http://example.com/success');

    expect($attachedPaymentIntent)
        ->id->toBe($paymentIntent->id)
        ->status->toBe('awaiting_next_action')
        ->next_action->toBeArray()->toMatchArray([
            'type' => 'redirect',
        ]);

    expect($attachedPaymentIntent->next_action['redirect'])
        ->toHaveKeys(['url', 'return_url']);
});
