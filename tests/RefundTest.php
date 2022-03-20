<?php

use Illuminate\Support\Collection;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Refund;

it('can create refund', function (string $reason) {
    $token = createToken();
    $payment = createPayment($token);

    $refund = Paymongo::refund()->create([
        'amount' => 10,
        'notes' => 'test refund',
        'payment_id' => $payment->id,
        'reason' => $reason,
    ]);

    expect($refund)->toBeInstanceOf(Refund::class)
        ->type->toBe('refund')
        ->amount->toBe(10.0)
        ->notes->toBe('test refund')
        ->payment_id->toBe($payment->id)
        ->reason->toBe($reason)
        ->status->toBe('pending');
})->with([
    'reason duplicate' => [Refund::REASON_DUPLICATE],
    'reason requested by customer' => [Refund::REASON_REQUESTED_BY_CUSTOMER],
    'reason fraudulent' => [Refund::REASON_FRAUDULENT],
    'reason others' => [Refund::REASON_OTHERS],
]);

it('can retrieve a refund', function () {
    $token = createToken();
    $payment = createPayment($token);

    $refund = Paymongo::refund()->create([
        'amount' => 10,
        'notes' => 'test refund',
        'payment_id' => $payment->id,
        'reason' => Refund::REASON_DUPLICATE,
    ]);

    $retrievedRefund = Paymongo::refund()->find($refund->id);

    expect($retrievedRefund)->toBeInstanceOf(Refund::class)
        ->type->toBe('refund')
        ->amount->toBe(10.0)
        ->notes->toBe('test refund')
        ->payment_id->toBe($payment->id)
        ->reason->toBe(Refund::REASON_DUPLICATE)
        ->status->toBe('succeeded');
});

it('can get all refunds', function () {
    $refunds = Paymongo::refund()->all();

    expect($refunds)->toBeInstanceOf(Collection::class)
       ->each
       ->toBeInstanceOf(Refund::class);
});
