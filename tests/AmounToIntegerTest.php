<?php

use Luigel\Paymongo\Exceptions\AmountTypeNotSupportedException;
use Luigel\Paymongo\Paymongo;

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

it('can change amount type from the config', function () {
    config(['paymongo.amount_type' => Paymongo::AMOUNT_TYPE_INT]);

    $payload = ['amount' => 10000];
    $convertedPayload = $this->convertPayloadAmountsToInteger($payload);

    expect($convertedPayload['amount'])->toBe(10000)->toBeInt();
});

it('will throw an exception if amount type is invalid or not supported', function () {
    config(['paymongo.amount_type' => 'test']);
    $payload = ['amount' => 10000];

    $this->expectException(AmountTypeNotSupportedException::class);
    $this->convertPayloadAmountsToInteger($payload);
});
