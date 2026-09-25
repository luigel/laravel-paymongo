<?php

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Exceptions\InvalidRequestException;
use Luigel\Paymongo\Facades\Paymongo;

it('fails the checkout when PayMongo rejects the amount', function () {
    Paymongo::fake([
        '*/checkout_sessions' => Http::response([
            'errors' => [['code' => 'parameter_below_minimum', 'detail' => 'The minimum value for the amount is 2000.']],
        ], 400),
    ]);

    $order = Order::create(['reference' => 'ORDER-1235', 'description' => 'Sticker', 'amount' => 1000]);

    $this->withoutExceptionHandling();

    expect(fn () => $this->post(route('orders.checkout', $order)))
        ->toThrow(InvalidRequestException::class, 'The minimum value for the amount is 2000.');
});
