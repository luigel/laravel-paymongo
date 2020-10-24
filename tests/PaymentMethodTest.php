<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\PaymentMethod;

class PaymentMethodTest extends BaseTestCase
{
    /** @test */
    public function it_can_create_payment_method()
    {
        $paymentMethod = Paymongo::paymentMethod()
                    ->create([
                        'type' => 'card',
                        'details' => [
                            'card_number' => $this::TEST_VISA_CARD_WITHOUT_3D_SECURE,
                            'exp_month' => 12,
                            'exp_year' => 25,
                            'cvc' => '123',
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
                            'phone' => '0935454875545',
                        ],
                    ]);

        $this->assertInstanceOf(PaymentMethod::class, $paymentMethod);
        $this->assertEquals($paymentMethod->type, 'payment_method');
        $this->assertEquals($paymentMethod->payment_method_type, 'card');
        $this->assertEquals($paymentMethod->details['last4'], $this::TEST_VISA_CARD_WITHOUT_3D_SECURE_LAST_4);
        $this->assertEquals($paymentMethod->details['exp_month'], 12);
        $this->assertEquals('Cebu City', $paymentMethod->billing['address']['city']);
    }

    /** @test */
    public function it_cannot_create_payment_method_with_invalid_data()
    {
        $this->expectException(BadRequestException::class);

        Paymongo::paymentMethod()
                    ->create([]);
    }

    /** @test */
    public function it_can_retrieve_payment_method()
    {
        $paymentMethodCreated = Paymongo::paymentMethod()
                    ->create([
                        'type' => 'card',
                        'details' => [
                            'card_number' => $this::TEST_VISA_CARD_WITHOUT_3D_SECURE,
                            'exp_month' => 12,
                            'exp_year' => 25,
                            'cvc' => '123',
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
                            'phone' => '0935454875545',
                        ],
                    ]);

        $paymentMethod = Paymongo::paymentMethod()->find($paymentMethodCreated->id);

        $this->assertEquals($paymentMethodCreated, $paymentMethod);
    }
}
