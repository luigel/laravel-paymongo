<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Token;

it('it can create token', function () {
    $token = Paymongo::token()
        ->create([
            'number' => getTestCardWithout3dSecure(),
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

    expect($token)
        ->toBeInstanceOf(Token::class)
        ->card->toBeArray()->toMatchArray([
            'last4' => '4345',
            'exp_month' => 12,
        ])
        ->billing->toBeArray()->toMatchArray([
            'name' => 'Rigel Kent Carbonel',
        ]);
});

it('can retrieve token', function () {
    $createdToken = Paymongo::token()
        ->create([
            'number' => getTestCardWithout3dSecure(),
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

    $token = Paymongo::token()->find($createdToken->id);

    $this->assertEquals($createdToken, $token);
});

it('cannot create token with invalid data', function () {
    $this->expectException(BadRequestException::class);

    Paymongo::token()
        ->create([
            'number' => '424242424242424222',
            'exp_month' => 12,
            'exp_year' => 25,
            'cvc' => '123',
        ]);
});
