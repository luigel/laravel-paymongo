<?php

//namespace Luigel\Paymongo\Tests;
//
//use Luigel\Paymongo\Traits\Request;
//
//class AmounToIntegerTest extends BaseTestCase
//{
//    use Request;
//
//    /** @test */
//    public function it_can_convert_without_decimal()
//    {
//
//    }
//
//    /** @test */
//    public function it_can_convert_with_in_tenth_decimal()
//    {
//
//    }
//
//    /** @test */
//    public function it_can_convert_with_in_hundredth_decimal()
//    {
//        $payload = ['amount' => 254.950];
//
//        $convertedPayload = $this->convertPayloadAmountsToInteger($payload);
//
//        $this->assertIsInt($convertedPayload['amount']);
//        $this->assertIsNotFloat($convertedPayload['amount']);
//    }
//}


it('can convert without decimal', function () {
    $payload = ['amount' => 147];

    $convertedPayload = $this->convertPayloadAmountsToInteger($payload);

    expect($convertedPayload['amount'])->toBeInt();
});

it('can convert with in tenth decimal', function () {
    $payload = ['amount' => 147.95];

    $convertedPayload = $this->convertPayloadAmountsToInteger($payload);

    expect($convertedPayload['amount'])->toBeInt()->toBe(14795);
});

it('can convert with in hundredth decimal', function () {
    $payload = ['amount' => 254.955];
    $convertedPayload = $this->convertPayloadAmountsToInteger($payload);
    expect($convertedPayload['amount'])->toBe(25496);

    $payload = ['amount' => 254.951];
    $convertedPayload = $this->convertPayloadAmountsToInteger($payload);
    expect($convertedPayload['amount'])->toBe(25495);
});
