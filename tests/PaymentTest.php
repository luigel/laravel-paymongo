<?php

namespace Luigel\LaravelPaymongo\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Support\Collection;
use Luigel\LaravelPaymongo\Exceptions\BadRequestException;
use Luigel\LaravelPaymongo\Exceptions\NotFoundException;
use Luigel\LaravelPaymongo\Exceptions\PaymentErrorException;
use Luigel\LaravelPaymongo\Facades\Paymongo;
use Orchestra\Testbench\TestCase;
use Luigel\LaravelPaymongo\LaravelPaymongoServiceProvider;
use Luigel\LaravelPaymongo\Models\Payment;
use Luigel\LaravelPaymongo\Models\PaymentSource;

class PaymentTest extends TestCase
{
    use InteractsWithExceptionHandling;

    protected function getPackageProviders($app)
    {
        return [LaravelPaymongoServiceProvider::class];
    }
    
    /** @test */
    public function it_can_create_payment()
    {
        $this->withoutExceptionHandling();

        $token = Paymongo::token()->create([
            'number' => '4242424242424242',
            'exp_month' => 12,
            'exp_year' => 25,
            'cvc' => "123",
        ]);

        $payment = Paymongo::payment()
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

        $this->assertTrue($payment->amount == 100.00);
        $this->assertTrue($payment->currency === 'PHP');
        $this->assertTrue($payment->statement_descriptor === 'Test Paymongo');
        $this->assertTrue($payment->status === 'paid');
        
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertInstanceOf(PaymentSource::class, $payment->source);
    }

    /** @test */
    public function it_cannot_create_payment_when_token_is_used_more_than_once()
    {
        $this->expectException(BadRequestException::class);

        $token = Paymongo::token()->create([
            'number' => '4242424242424242',
            'exp_month' => 12,
            'exp_year' => 25,
            'cvc' => "123",
        ]);

        $payment = Paymongo::payment()
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
        $this->assertTrue($payment->amount == 100.00);
        $this->assertTrue($payment->currency === 'PHP');
        $this->assertTrue($payment->statement_descriptor === 'Test Paymongo');
        $this->assertTrue($payment->status === 'paid');

        $payment = Paymongo::payment()
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
    }

    /** @test */
    public function it_cannot_create_payment_when_token_is_not_valid()
    {
        $this->expectException(PaymentErrorException::class);

        $token = Paymongo::token()->create([
            'number' => '4444333322221111',
            'exp_month' => 12,
            'exp_year' => 25,
            'cvc' => "123",
        ]);

        $payment = Paymongo::payment()
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

    }

    /** @test */
    public function it_can_retrieve_a_payment()
    {
        $token = Paymongo::token()->create([
            'number' => '4242424242424242',
            'exp_month' => 12,
            'exp_year' => 25,
            'cvc' => "123",
        ]);

        $createdPayment = Paymongo::payment()
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

        $payment = Paymongo::payment()
            ->find($createdPayment->id);

        $this->assertEquals($createdPayment, $payment);
    }

        /** @test */
        public function it_can_not_retrieve_a_payment_with_invalid_id()
        {
            $this->expectException(NotFoundException::class);
            
            $token = Paymongo::token()->create([
                'number' => '4242424242424242',
                'exp_month' => 12,
                'exp_year' => 25,
                'cvc' => "123",
            ]);
    
            Paymongo::payment()
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
    
            Paymongo::payment()
                ->find('test');
        }

        /** @test */
        public function it_can_get_all_payments()
        {
            $payments = Paymongo::payment()->all();

            $this->assertInstanceOf(Collection::class, $payments);
        }

}
