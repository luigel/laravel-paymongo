<?php

namespace Luigel\LaravelPaymongo\Tests;

use Luigel\LaravelPaymongo\Facades\PaymongoFacade;
use Orchestra\Testbench\TestCase;
use Luigel\LaravelPaymongo\LaravelPaymongoServiceProvider;

class PaymentTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelPaymongoServiceProvider::class];
    }
    
    /** @test */
    public function it_can_create_payment()
    {
        $token = PaymongoFacade::token()->create([
            'number' => '4242424242424242',
            'exp_month' => 12,
            'exp_year' => 25,
            'cvc' => "123",
        ]);

        $payment = PaymongoFacade::payment()
                    ->create([
                        'amount' => '100.00',
                        'currency' => 'PHP',
                        'description' => 'Testing payment',
                        'statement_descriptor' => 'Test Paymongo',
                        'source' => [
                            'id' => $token->id,
                            'type' => $token->type
                        ]
                    ]);

        $this->assertTrue($payment->amount === 100.00);
        $this->assertTrue($payment->currency === 'PHP');
        $this->assertTrue($payment->statement_descriptor === 'Test Paymongo');
        // $this->assertTrue($payment->status === 'PHP');
    }
}
