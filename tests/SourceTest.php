<?php

use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Source;

it('can create a gcash source', function () {
    $source = Paymongo::source()->create([
        'type' => 'gcash',
        'amount' => 100.00,
        'currency' => 'PHP',
        'redirect' => [
            'success' => 'http://localhost/success',
            'failed' => 'http://localhost/failed',
        ],
    ]);

    expect($source)
        ->toBeInstanceOf(Source::class)
        ->source_type->toBe('gcash')
        ->type->toBe('source')
        ->amount->toBe(100.00)
        ->redirect->toBeArray()->toMatchArray([
            'success' => 'http://localhost/success',
            'failed' => 'http://localhost/failed',
            'checkout_url' => $source->redirect['checkout_url'],
        ]);
});

it('can create a grab pay source', function () {
    $source = Paymongo::source()->create([
        'type' => 'grab_pay',
        'amount' => 100.00,
        'currency' => 'PHP',
        'redirect' => [
            'success' => 'http://localhost/success',
            'failed' => 'http://localhost/failed',
        ],
    ]);

    expect($source)
        ->toBeInstanceOf(Source::class)
        ->source_type->toBe('grab_pay')
        ->type->toBe('source')
        ->amount->toBe(100.00)
        ->redirect->toBeArray()->toMatchArray([
            'success' => 'http://localhost/success',
            'failed' => 'http://localhost/failed',
            'checkout_url' => $source->redirect['checkout_url'],
        ]);
});
