<?php

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
