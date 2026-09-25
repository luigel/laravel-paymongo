<?php

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Facades\Paymongo;

it('makes exactly one PayMongo request per checkout, and no other HTTP call', function () {
    Http::preventStrayRequests();
    Paymongo::fake();

    $order = Order::create(['reference' => 'ORDER-1234', 'description' => 'Leather wallet', 'amount' => 150050]);

    $this->post(route('orders.checkout', $order))->assertRedirect();

    Http::assertSentCount(1);
});
