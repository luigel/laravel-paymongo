<?php

namespace Luigel\LaravelPaymongo\Tests;

use Luigel\LaravelPaymongo\Facades\PaymongoFacade;
use Orchestra\Testbench\TestCase;
use Luigel\LaravelPaymongo\LaravelPaymongoServiceProvider;

class TokenTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelPaymongoServiceProvider::class];
    }
    
    /** @test */
    public function it_can_create_token()
    {
        $token = PaymongoFacade::token()
                    ->create([
                        'number' => '4242424242424242',
                        'exp_month' => 12,
                        'exp_year' => 25,
                        'cvc' => "123",
                    ]);

        $this->assertTrue($token->card['last4'] === '4242');
        $this->assertTrue($token->card['exp_month'] === 12);
    }
    
    /** @test */
    public function it_cannot_create_token_with_invalid_data()
    {
        $token = PaymongoFacade::token()
                    ->create([
                        'number' => '424242424242424222',
                        'exp_month' => 12,
                        'exp_year' => 25,
                        'cvc' => "123",
                        'name' => 'Rigel Kent Carbonel',
                        'email' => 'rigel20.kent@gmail.com',
                        'phone' => 'test'
        ]);
        
        $this->assertEquals('Bad Request', $token);

    }
}
