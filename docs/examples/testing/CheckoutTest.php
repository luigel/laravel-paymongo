<?php

use App\Models\Order;
use Illuminate\Http\Client\Request;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Testing\Fixtures;

it('sends the customer to PayMongo to pay for their order', function () {
    Paymongo::fake([
        '*/checkout_sessions' => Fixtures::checkoutSession(['checkout_url' => 'https://checkout.paymongo.com/cs_test']),
    ]);

    $order = Order::create(['reference' => 'ORDER-1234', 'description' => 'Leather wallet', 'amount' => 150050]);

    $this->post(route('orders.checkout', $order))
        ->assertRedirect('https://checkout.paymongo.com/cs_test');

    Paymongo::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/checkout_sessions')
        && $request['data']['attributes']['reference_number'] === 'ORDER-1234'
        && $request['data']['attributes']['line_items'][0]['amount'] === 150050);
});

it('sends nothing to PayMongo for an order that does not exist', function () {
    Paymongo::fake();

    $this->post('/orders/999/checkout')->assertNotFound();

    Paymongo::assertNothingSent();
});
