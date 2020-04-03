<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Facades\Paymongo;
use Orchestra\Testbench\TestCase;
use Luigel\Paymongo\PaymongoServiceProvider;
use Luigel\Paymongo\Models\Card;
use Luigel\Paymongo\Models\Token;

class TokenTest extends BaseTest
{
    /** @test */
    public function it_can_create_token()
    {
        $token = Paymongo::token()
                    ->create([
                        'number' => $this::TEST_VISA_CARD_WITHOUT_3D_SECURE,
                        'exp_month' => 12,
                        'exp_year' => 25,
                        'cvc' => "123",
                    ]);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertInstanceOf(Card::class, $token->card);
        $this->assertEquals($token->card->last4, '4242');
        $this->assertEquals($token->card->exp_month, 12);
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
                        'cvc' => "123",
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
                        'cvc' => "123",
        ]);

        $token = Paymongo::token()->find($createdToken->id);

        $this->assertEquals($createdToken, $token);
    }
}
