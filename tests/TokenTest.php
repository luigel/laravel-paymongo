<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Token;

class TokenTest extends BaseTestCase
{
    /** @test */
    public function it_can_create_token()
    {
        $token = Paymongo::token()
            ->create([
                'number' => $this::TEST_VISA_CARD_WITHOUT_3D_SECURE,
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

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals($token->card['last4'], '4345');
        $this->assertEquals($token->card['exp_month'], 12);
        $this->assertEquals($token->billing['name'], 'Rigel Kent Carbonel');
    }

    /** @test */
    public function it_cannot_create_token_with_invalid_data()
    {
        $this->expectException(BadRequestException::class);

        Paymongo::token()
            ->create([
                'number' => '424242424242424222',
                'exp_month' => 12,
                'exp_year' => 25,
                'cvc' => '123',
            ]);
    }

    /** @test */
    public function it_can_retrieve_token()
    {
        $createdToken = Paymongo::token()
            ->create([
                'number' => $this::TEST_VISA_CARD_WITHOUT_3D_SECURE,
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
    }
}
