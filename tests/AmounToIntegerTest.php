<?php
namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Models\BaseModel;
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
}
