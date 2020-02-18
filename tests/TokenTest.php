<?php

namespace Luigel\LaravelPaymongo\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Luigel\LaravelPaymongo\LaravelPaymongoServiceProvider;
use Luigel\LaravelPaymongo\Models\Token;
use Luigel\LaravelPaymongo\Paymongo;

class TokenTest extends TestCase
{
    use RefreshDatabase, InteractsWithExceptionHandling;

    protected function getPackageProviders($app)
    {
        return [LaravelPaymongoServiceProvider::class];
    }
    
    /** @test */
    public function it_can_create_token()
    {
        $this->withoutExceptionHandling();

        $token = (new Paymongo())->token()
                    ->create([
                        // 'card' => [
                            'number' => '4242424242424242',
                            'exp_month' => 12,
                            'exp_year' => 25,
                            'cvc' => "123",
                        // ],
                        // 'name' => 'Rigel Kent Carbonel',
                        // 'email' => 'rigel20.kent@gmail.com',
                        // 'phone' => 'test'
                    ]);
        
        // $this->assertInstanceOf(Token::class, get_class($token));
    }
}
