<?php

declare(strict_types=1);

use App\Listeners\FulfillOrder;
use App\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Luigel\Paymongo\Events\CheckoutSessionPaymentPaid;
use Luigel\Paymongo\Tests\ContractTestCase;
use Luigel\Paymongo\Tests\TestCase;

uses(TestCase::class)->in('Unit', 'Feature');
uses(ContractTestCase::class)->in('Contract');

/**
 * Load a fixture array from tests/Fixtures/{name}.php.
 *
 * Named fixture_data() because Pest v4 already declares a global fixture()
 * helper (returning a fixture file path) that always loads first.
 *
 * @return array<string, mixed>
 */
function fixture_data(string $name): array
{
    return require __DIR__.'/Fixtures/'.$name.'.php';
}

/**
 * Run a Package docs example file (docs/examples) in its own scope, the way
 * a reader would paste it into their app.
 */
function run_docs_example(string $path): void
{
    (static function () use ($path): void {
        require $path;
    })();
}

/**
 * Boot the Your first payment tutorial's app (docs/examples/first-payment):
 * its orders table, checkout routes, and webhook listener, as they would
 * run in the reader's app.
 */
function boot_docs_first_payment_app(): void
{
    $app = dirname(__DIR__).'/docs/examples/first-payment';

    create_docs_orders_table();

    require_once $app.'/CheckoutController.php';
    require_once $app.'/FulfillOrder.php';

    // The web middleware group encrypts cookies, as in the reader's app.
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::middleware('web')->group(function () use ($app) {
        Route::get('/orders/{order}', fn (Order $order): string => $order->reference)->name('orders.show');

        require $app.'/routes.php';
    });

    Route::getRoutes()->refreshNameLookups();

    Event::listen(CheckoutSessionPaymentPaid::class, FulfillOrder::class);
}

/**
 * Create the orders table and load the Order model the docs examples assume
 * the reader's app already has (tests/Fixtures/DocsApp/Order.php).
 */
function create_docs_orders_table(): void
{
    require_once __DIR__.'/Fixtures/DocsApp/Order.php';

    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('reference')->unique();
        $table->string('description');
        $table->unsignedInteger('amount');
        $table->string('payment_id')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();
    });
}

/**
 * The server variables of a webhook delivery PayMongo signed with the
 * given endpoint secret, for `$this->call('POST', ..., server: ...)`.
 *
 * @return array<string, string>
 */
function signed_webhook_server(string $payload, string $secret = 'whsk_test_fake'): array
{
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$signature},li=",
    ];
}
