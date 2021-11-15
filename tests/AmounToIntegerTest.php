<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Exceptions\AmountTypeNotSupportedException;
use Luigel\Paymongo\Paymongo;
use Luigel\Paymongo\Traits\Request;

class AmounToIntegerTest extends BaseTestCase
{
    use Request;

    /** @test */
    public function it_can_convert_without_decimal()
    {
        $payload = ['amount' => 147];

        $convertedPayload = $this->convertPayloadAmountsToInteger($payload);

        $this->assertIsInt($convertedPayload['amount']);
        $this->assertIsNotFloat($convertedPayload['amount']);
    }

    /** @test */
    public function it_can_convert_with_in_tenth_decimal()
    {
        $payload = ['amount' => 147.95];

        $convertedPayload = $this->convertPayloadAmountsToInteger($payload);

        $this->assertIsInt($convertedPayload['amount']);
        $this->assertIsNotFloat($convertedPayload['amount']);
    }

    /** @test */
    public function it_can_convert_with_in_hundredth_decimal()
    {
        $payload = ['amount' => 254.950];

        $convertedPayload = $this->convertPayloadAmountsToInteger($payload);

        $this->assertIsInt($convertedPayload['amount']);
        $this->assertIsNotFloat($convertedPayload['amount']);
    }

    /** @test */
    public function it_can_change_amount_type_from_the_config()
    {
        config(['paymongo.amount_type' => Paymongo::AMOUNT_TYPE_INT]);

        $payload = ['amount' => 10000];
        $convertedPayload = $this->convertPayloadAmountsToInteger($payload);

        $this->assertIsInt($convertedPayload['amount']);
        $this->assertEquals(10000, $convertedPayload['amount']);
    }

    /** @test */
    public function it_will_throw_an_exception_if_amount_type_is_invalid_or_not_supported()
    {
        config(['paymongo.amount_type' => 'test']);
        $payload = ['amount' => 10000];

        $this->expectException(AmountTypeNotSupportedException::class);
        $this->convertPayloadAmountsToInteger($payload);
    }
}
