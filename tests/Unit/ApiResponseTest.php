<?php

declare(strict_types=1);

use Luigel\Paymongo\Client\ApiResponse;

it('exposes a single resource payload', function () {
    $response = new ApiResponse([
        'data' => ['id' => 'pi_1', 'type' => 'payment_intent', 'attributes' => []],
    ], 200);

    expect($response->status)->toBe(200)
        ->and($response->data())->toBe(['id' => 'pi_1', 'type' => 'payment_intent', 'attributes' => []])
        ->and($response->isList())->toBeFalse()
        ->and($response->hasMore())->toBeFalse();
});

it('exposes a list payload with has_more', function () {
    $response = new ApiResponse([
        'data'     => [['id' => 'pay_1'], ['id' => 'pay_2']],
        'has_more' => true,
    ], 200);

    expect($response->data())->toHaveCount(2)
        ->and($response->isList())->toBeTrue()
        ->and($response->hasMore())->toBeTrue();
});

it('returns an empty array when data is missing', function () {
    $response = new ApiResponse([], 200);

    expect($response->data())->toBe([])
        ->and($response->hasMore())->toBeFalse();
});
