<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Exceptions\NotFoundException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\PaymentIntent;

class PaymentIntentTest extends BaseTestCase
{
    /** @test */
    public function it_can_create_payment_intent()
    {
        $paymentIntent = Paymongo::paymentIntent()->create([
            'amount' => 100,
            'payment_method_allowed' => [
                'card'
            ],
            'payment_method_options' => [
                'card' => [
                    'request_three_d_secure' => 'automatic'
                ]
            ],
            'description' => 'This is a test payment intent',
            'statement_descriptor' => 'LUIGEL STORE',
            'currency' => "PHP",
        ]);

        $this->assertEquals(100.00, $paymentIntent->getAmount());
        $this->assertEquals('awaiting_payment_method', $paymentIntent->getStatus());
        $this->assertTrue($paymentIntent->getStatementDescriptor() === 'LUIGEL STORE');
        $this->assertTrue($paymentIntent->getType() === 'payment_intent');

        $this->assertInstanceOf(PaymentIntent::class, $paymentIntent);
    }

    /** @test */
    public function it_cannot_create_payment_intent_when_data_is_invalid()
    {
        $this->expectException(BadRequestException::class);

        Paymongo::paymentIntent()->create([]);
    }

    /** @test */
    public function it_can_cancel_payment_intent()
    {
        $paymentIntent = Paymongo::paymentIntent()->create([
            'amount' => 100,
            'payment_method_allowed' => [
                'card'
            ],
            'payment_method_options' => [
                'card' => [
                    'request_three_d_secure' => 'automatic'
                ]
            ],
            'description' => 'This is a test payment intent',
            'statement_descriptor' => 'LUIGEL STORE',
            'currency' => "PHP",
        ]);

        $cancelledPaymentIntent = $paymentIntent->cancel();

        $this->assertEquals('cancelled', $cancelledPaymentIntent->getStatus());
        $this->assertEquals($paymentIntent->getId(), $cancelledPaymentIntent->getId());
    }

    /** @test */
    public function it_can_attach_payment_method_to_payment_intent()
    {
        $paymentIntent = Paymongo::paymentIntent()
            ->create([
                'amount' => 100,
                'payment_method_allowed' => [
                    'card'
                ],
                'payment_method_options' => [
                    'card' => [
                        'request_three_d_secure' => 'automatic'
                    ]
                ],
                'description' => 'This is a test payment intent',
                'statement_descriptor' => 'LUIGEL STORE',
                'currency' => "PHP",
            ]);

        $paymentMethod = Paymongo::paymentMethod()
            ->create([
                'type' => 'card',
                'details' => [
                    'card_number' => $this::TEST_VISA_CARD_WITHOUT_3D_SECURE,
                    'exp_month' => 12,
                    'exp_year' => 25,
                    'cvc' => "123",
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
                    'phone' => '0935454875545'
                ],
            ]);

            $attachedPaymentIntent = $paymentIntent->attach($paymentMethod->getId());

            $this->assertEquals($paymentIntent->getId(), $attachedPaymentIntent->getId());
            $this->assertEquals('succeeded', $attachedPaymentIntent->getStatus());
    }

    /** @test */
    public function it_cannot_attach_payment_intent_with_invalid_data()
    {
        $this->expectException(NotFoundException::class);

        Paymongo::paymentIntent()->find('test')->attach('test');
    }

    /** @test */
    public function it_can_retrieve_payment_intent()
    {
        $paymentIntent = Paymongo::paymentIntent()
            ->create([
                'amount' => 100,
                'payment_method_allowed' => [
                    'card'
                ],
                'payment_method_options' => [
                    'card' => [
                        'request_three_d_secure' => 'automatic'
                    ]
                ],
                'description' => 'This is a test payment intent',
                'statement_descriptor' => 'LUIGEL STORE',
                'currency' => "PHP",
            ]);

            $retrievedPaymentIntent = Paymongo::paymentIntent()->find($paymentIntent->getId());

            $this->assertEquals($paymentIntent, $retrievedPaymentIntent);
    }
}
