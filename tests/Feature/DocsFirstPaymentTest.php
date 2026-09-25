<?php

declare(strict_types=1);

use App\Models\Order;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Testing\Fixtures;

/*
 * The Your first payment tutorial's examples (docs/examples/first-payment)
 * are the files of one small app: a checkout controller, its routes, and a
 * webhook listener. They run here together, end to end, as they would in
 * the reader's app.
 */

beforeEach(function () {
    boot_docs_first_payment_app();

    $this->order = Order::create(['reference' => 'ORDER-1234', 'description' => 'Leather wallet', 'amount' => 150050]);
});

it('sends the customer to a checkout session for their order', function () {
    Paymongo::fake([
        '*/checkout_sessions' => Fixtures::checkoutSession(['checkout_url' => 'https://checkout.paymongo.com/cs_1']),
    ]);

    $this->post("/orders/{$this->order->id}/checkout")
        ->assertRedirect('https://checkout.paymongo.com/cs_1');

    Paymongo::assertSent(fn ($request): bool => $request['data']['attributes']['reference_number'] === 'ORDER-1234'
        && $request['data']['attributes']['line_items'][0]['amount'] === 150050
        && $request['data']['attributes']['success_url'] === route('orders.show', $this->order));
});

it('fulfils the order when PayMongo reports its checkout session paid', function () {
    $payload = json_encode(Fixtures::event('checkout_session.payment.paid', Fixtures::checkoutSession([
        'reference_number' => 'ORDER-1234',
    ])), JSON_THROW_ON_ERROR);

    $this->call('POST', '/paymongo/webhook', server: signed_webhook_server($payload), content: $payload)->assertOk();

    expect($this->order->fresh()->paid_at)->not->toBeNull();
});
