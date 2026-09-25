<?php

use App\Listeners\FulfillOrder;
use App\Models\Order;
use Luigel\Paymongo\Events\CheckoutSessionPaymentPaid;
use Luigel\Paymongo\Testing\Fixtures;
use Luigel\Paymongo\Webhooks\WebhookEvent;

it('marks the order paid when PayMongo delivers checkout_session.payment.paid', function () {
    $order = Order::create(['reference' => 'ORDER-1234', 'description' => 'Leather wallet', 'amount' => 150050]);

    $payload = json_encode(Fixtures::event('checkout_session.payment.paid', Fixtures::checkoutSession([
        'reference_number' => 'ORDER-1234',
    ])));

    // Sign it the way PayMongo does, with your endpoint's secret.
    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", config('paymongo.webhooks.secret'));

    $this->call('POST', '/paymongo/webhook', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$signature},li=",
    ], content: $payload)->assertOk();

    expect($order->fresh()->paid_at)->not->toBeNull();
});

it('fulfils the order in the listener', function () {
    $order = Order::create(['reference' => 'ORDER-1234', 'description' => 'Leather wallet', 'amount' => 150050]);

    $payload = Fixtures::event('checkout_session.payment.paid', Fixtures::checkoutSession([
        'reference_number' => 'ORDER-1234',
    ]));

    (new FulfillOrder)->handle(new CheckoutSessionPaymentPaid(WebhookEvent::fromArray($payload)));

    expect($order->fresh()->paid_at)->not->toBeNull();
});
