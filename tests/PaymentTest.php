<?php

namespace Luigel\Paymongo\Tests;

use Illuminate\Support\Collection;
use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Exceptions\NotFoundException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Payment;

class PaymentTest extends BaseTestCase
{

    protected $token;

    public function setUp(): void
    {
        parent::setUp();
        $this->token = Paymongo::token()->create([
            'number' => $this::TEST_VISA_CARD_WITHOUT_3D_SECURE,
            'exp_month' => 12,
            'exp_year' => 25,
            'cvc' => "123",
            'billing' => [
                'address' => [
                    'line1' => 'Test Address',
                    'city' => 'Cebu City',
                    'postal_code' => '6000',
                    'country' => 'PH'
                ],
                'name' => 'Rigel Kent Carbonel',
                'email' => 'rigel20.kent@gmail.com',
                'phone' => '928392893'
            ]
        ]);
    }

    /** @test */
    public function it_can_create_payment()
    {
        $payment = Paymongo::payment()
            ->create([
                'amount' => 100.00,
                'currency' => 'PHP',
                'description' => 'Testing payment',
                'statement_descriptor' => 'Test Paymongo',
                'source' => [
                    'id' => $this->token->getId(),
                    'type' => $this->token->getType()
                ]
            ]);

        $this->assertTrue($payment->getAmount() == 100.00);
        $this->assertTrue($payment->getCurrency() === 'PHP');
        $this->assertTrue($payment->getStatementDescriptor() === 'Test Paymongo');
        $this->assertTrue($payment->getStatus() === 'paid');

        $this->assertInstanceOf(Payment::class, $payment);
    }

    /** @test */
    public function it_cannot_create_payment_when_token_is_used_more_than_once()
    {
        $this->expectException(BadRequestException::class);

        $payment = Paymongo::payment()
            ->create([
                'amount' => 100.00,
                'currency' => 'PHP',
                'description' => 'Testing payment',
                'statement_descriptor' => 'Test Paymongo',
                'source' => [
                    'id' => $this->token->getId(),
                    'type' => $this->token->getType()
                ]
            ]);
        $this->assertTrue($payment->getAmount() === 100.00);
        $this->assertTrue($payment->getCurrency() === 'PHP');
        $this->assertTrue($payment->getStatementDescriptor() === 'Test Paymongo');
        $this->assertTrue($payment->getStatus() === 'paid');

        $payment = Paymongo::payment()
            ->create([
                'amount' => '100.00',
                'currency' => 'PHP',
                'description' => 'Testing payment',
                'statement_descriptor' => 'Test Paymongo',
                'source' => [
                    'id' => $this->token->getId(),
                    'type' => $this->token->getType()
                ]
            ]);
    }

    /** @test */
    public function it_cannot_create_payment_when_token_is_not_valid()
    {        
        $this->expectException(BadRequestException::class);

        $token = Paymongo::token()->create([
            'number' => '5100000000000198',
            'exp_month' => 12,
            'exp_year' => 25,
            'cvc' => "123",
            'billing' => [
                'address' => [
                    'line1' => 'Test Address',
                    'city' => 'Cebu City',
                    'postal_code' => '6000',
                    'country' => 'PH'
                ],
                'name' => 'Rigel Kent Carbonel',
                'email' => 'rigel20.kent@gmail.com',
                'phone' => '928392893'
            ]
        ]);

        Paymongo::payment()
            ->create([
                'amount' => 100.00,
                'currency' => 'PHP',
                'description' => 'Testing payment',
                'statement_descriptor' => 'Test Paymongo',
                'source' => [
                    'id' => $token->getId(),
                    'type' => $token->getType()
                ]
            ]);
    }

    /** @test */
    public function it_can_retrieve_a_payment()
    {
        $createdPayment = Paymongo::payment()
            ->create([
                'amount' => 100.00,
                'currency' => 'PHP',
                'description' => 'Testing payment',
                'statement_descriptor' => 'Test Paymongo',
                'source' => [
                    'id' => $this->token->getId(),
                    'type' => $this->token->getType()
                ]
            ]);

        $payment = Paymongo::payment()
            ->find($createdPayment->getId());

        $this->assertEquals($createdPayment, $payment);
    }

    /** @test */
    public function it_can_not_retrieve_a_payment_with_invalid_id()
    {
        $this->expectException(NotFoundException::class);

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
