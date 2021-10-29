<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Exceptions\UnauthorizedException;
use Luigel\Paymongo\Facades\Paymongo;

it('it expects unauthorized exception with invalid api keys', function () {
    config(['paymongo.secret_key' => 'invalid']);

    $this->expectException(UnauthorizedException::class);

    Paymongo::token()
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
});
