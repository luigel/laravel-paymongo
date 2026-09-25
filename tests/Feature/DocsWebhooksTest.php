<?php

declare(strict_types=1);

use App\Listeners\MarkOrderPaid;
use App\Listeners\RecordWebhookEvent;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Luigel\Paymongo\Events\PaymentPaid;
use Luigel\Paymongo\Events\WebhookReceived;
use Luigel\Paymongo\Testing\Fixtures;

/*
 * The Webhooks page's receiving examples (docs/examples/receiving-webhooks)
 * are the routes and listeners of the reader's app. They run here against
 * signed deliveries, as PayMongo would send them.
 */

/**
 * The path of one of the Webhooks page's receiving examples.
 */
function receiving_webhooks_example(string $file): string
{
    return dirname(__DIR__, 2).'/docs/examples/receiving-webhooks/'.$file;
}

/**
 * The JSON body of a payment.paid delivery for order ORDER-1234.
 */
function payment_paid_payload(int $amount = 150050): string
{
    return json_encode(Fixtures::event('payment.paid', Fixtures::payment([
        'amount' => $amount,
        'metadata' => ['order_reference' => 'ORDER-1234'],
    ])), JSON_THROW_ON_ERROR);
}

beforeEach(function () {
    create_docs_orders_table();

    require_once receiving_webhooks_example('MarkOrderPaid.php');
    require_once receiving_webhooks_example('RecordWebhookEvent.php');

    $this->order = Order::create(['reference' => 'ORDER-1234', 'description' => 'Leather wallet', 'amount' => 150050]);
});

it('marks the order paid once, however often PayMongo delivers payment.paid', function () {
    require receiving_webhooks_example('routes.php');
    Event::listen(PaymentPaid::class, MarkOrderPaid::class);

    $payload = payment_paid_payload();

    $this->call('POST', '/paymongo/webhook', server: signed_webhook_server($payload), content: $payload)->assertOk();
    $paidAt = $this->order->fresh()->paid_at;

    $this->travel(5)->minutes();
    $this->call('POST', '/paymongo/webhook', server: signed_webhook_server($payload), content: $payload)->assertOk();

    expect($this->order->fresh())
        ->paid_at->toEqual($paidAt)
        ->payment_id->toBe('pay_hvTn9EyxduZ9gV8WHhSGYqBi');
});

it('ignores a payment.paid whose amount does not match the order', function () {
    require receiving_webhooks_example('routes.php');
    Event::listen(PaymentPaid::class, MarkOrderPaid::class);

    $payload = payment_paid_payload(amount: 100);

    $this->call('POST', '/paymongo/webhook', server: signed_webhook_server($payload), content: $payload)->assertOk();

    expect($this->order->fresh()->paid_at)->toBeNull();
});

it('rejects a delivery not signed with the webhook secret', function () {
    require receiving_webhooks_example('routes.php');
    Event::fake([WebhookReceived::class]);

    $payload = payment_paid_payload();

    $this->call('POST', '/paymongo/webhook', server: signed_webhook_server($payload, 'whsk_someone_else'), content: $payload)
        ->assertUnauthorized();

    Event::assertNotDispatched(WebhookReceived::class);
});

it('records every event, including ones the package has no class for', function () {
    require receiving_webhooks_example('routes.php');
    Event::listen(WebhookReceived::class, RecordWebhookEvent::class);
    Log::spy();

    $payload = json_encode(Fixtures::event('wallet.transaction.created', Fixtures::payment()), JSON_THROW_ON_ERROR);

    $this->call('POST', '/paymongo/webhook', server: signed_webhook_server($payload), content: $payload)->assertOk();

    Log::shouldHaveReceived('info')->once()->with('PayMongo event wallet.transaction.created', Mockery::on(
        fn (array $context): bool => $context['known'] === false && $context['resource_id'] === 'pay_hvTn9EyxduZ9gV8WHhSGYqBi',
    ));
});

it('verifies a second endpoint with its own named secret, under its own route name', function () {
    config(['paymongo.webhooks.secrets.orders' => 'whsk_test_orders']);
    require receiving_webhooks_example('multiple-endpoints.php');
    Route::getRoutes()->refreshNameLookups();
    Event::fake([PaymentPaid::class]);

    $payload = payment_paid_payload();

    expect(route('paymongo.webhooks.orders', absolute: false))->toBe('/webhooks/orders');

    $this->call('POST', '/webhooks/orders', server: signed_webhook_server($payload), content: $payload)->assertUnauthorized();
    $this->call('POST', '/webhooks/orders', server: signed_webhook_server($payload, 'whsk_test_orders'), content: $payload)->assertOk();

    Event::assertDispatchedTimes(PaymentPaid::class, 1);
});

it('verifies the signature on a route of your own', function () {
    require receiving_webhooks_example('custom-route.php');
    Log::spy();

    $payload = payment_paid_payload();

    $this->call('POST', '/webhooks/paymongo/ledger', server: signed_webhook_server($payload, 'whsk_nope'), content: $payload)->assertUnauthorized();
    $this->call('POST', '/webhooks/paymongo/ledger', server: signed_webhook_server($payload), content: $payload)->assertOk();

    Log::shouldHaveReceived('info')->once()->with('Ledger entry for pay_hvTn9EyxduZ9gV8WHhSGYqBi', ['amount' => 150050]);
});

it('verifies each merchant\'s deliveries with that merchant\'s own secret', function () {
    require_once dirname(__DIR__).'/Fixtures/DocsApp/Merchant.php';
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
    Schema::create('merchants', function (Blueprint $table) {
        $table->id();
        $table->text('paymongo_webhook_secret');
        $table->timestamps();
    });
    $merchant = Merchant::create(['paymongo_webhook_secret' => 'whsk_test_merchant']);

    Route::middleware(SubstituteBindings::class)->group(fn () => require receiving_webhooks_example('per-account-route.php'));
    Event::fake([WebhookReceived::class]);

    $payload = payment_paid_payload();

    $this->call('POST', "/webhooks/paymongo/{$merchant->id}", server: signed_webhook_server($payload), content: $payload)->assertUnauthorized();
    $this->call('POST', "/webhooks/paymongo/{$merchant->id}", server: signed_webhook_server($payload, 'whsk_test_merchant'), content: $payload)->assertOk();

    Event::assertDispatchedTimes(WebhookReceived::class, 1);
});
