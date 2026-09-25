<?php

declare(strict_types=1);

use App\Listeners\FulfillOrder;
use App\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Luigel\Paymongo\Events\CheckoutSessionPaymentPaid;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Testing\Fixtures;

/*
 * The Your first payment tutorial's examples (docs/examples/first-payment)
 * are the files of one small app: a checkout controller, its routes, and a
 * webhook listener. They run here together, end to end, as they would in
 * the reader's app.
 */

beforeEach(function () {
    $examples = dirname(__DIR__, 2).'/docs/examples/first-payment';

    require_once dirname(__DIR__).'/Fixtures/DocsApp/Order.php';
    require_once $examples.'/CheckoutController.php';
    require_once $examples.'/FulfillOrder.php';

    // The web middleware group encrypts cookies, as in the reader's app.
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('reference')->unique();
        $table->string('description');
        $table->unsignedInteger('amount');
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();
    });

    Route::middleware('web')->group(function () use ($examples) {
        Route::get('/orders/{order}', fn (Order $order): string => $order->reference)->name('orders.show');

        require $examples.'/routes.php';
    });

    Event::listen(CheckoutSessionPaymentPaid::class, FulfillOrder::class);

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
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsk_test_fake');

    $this->call('POST', '/paymongo/webhook', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$signature},li=",
    ], content: $payload)->assertOk();

    expect($this->order->fresh()->paid_at)->not->toBeNull();
});
