<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Luigel\Paymongo\Events\PaymentPaid;
use Luigel\Paymongo\Events\WebhookReceived;
use Luigel\Paymongo\Http\Controllers\WebhookController;

/**
 * Build a `Paymongo-Signature` header signing $payload at $timestamp.
 */
function macro_signature_header(string $payload, string $secret, int $timestamp): string
{
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return "t={$timestamp},te={$signature},li=";
}

it('registers the named webhook route with the signature middleware and CSRF exclusion', function () {
    $route = Route::paymongoWebhooks();

    // Applications refresh name lookups after their route files load; do the
    // same for a route registered mid-test.
    Route::getRoutes()->refreshNameLookups();

    expect($route)->toBeInstanceOf(RoutingRoute::class)
        ->and(Route::has('paymongo.webhooks'))->toBeTrue()
        ->and($route->getName())->toBe('paymongo.webhooks')
        ->and($route->uri())->toBe('paymongo/webhook')
        ->and($route->methods())->toContain('POST')
        ->and($route->getActionName())->toBe(WebhookController::class)
        ->and($route->gatherMiddleware())->toContain('paymongo.signature')
        ->and($route->excludedMiddleware())->toContain(ValidateCsrfToken::class);
});

it('accepts a custom uri and appends the named secret to the middleware', function () {
    $route = Route::paymongoWebhooks('hooks/paymongo', 'orders');

    expect($route->uri())->toBe('hooks/paymongo')
        ->and($route->gatherMiddleware())->toContain('paymongo.signature:orders');
});

it('routes a correctly signed request through the middleware to the controller', function () {
    Route::paymongoWebhooks();

    Event::fake();

    $body = json_encode(fixture_data('webhook_event'), JSON_THROW_ON_ERROR);
    $header = macro_signature_header($body, 'whsk_test_fake', time());

    $this->call('POST', '/paymongo/webhook', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_PAYMONGO_SIGNATURE' => $header,
    ], content: $body)
        ->assertOk()
        ->assertExactJson(['received' => true]);

    Event::assertDispatched(WebhookReceived::class);
    Event::assertDispatched(PaymentPaid::class, fn (PaymentPaid $event): bool => $event->event->resourceId() === 'pay_hvTn9EyxduZ9gV8WHhSGYqBi');
});

it('rejects an unsigned request to the macro route', function () {
    Route::paymongoWebhooks();

    Event::fake();

    $this->postJson('/paymongo/webhook', fixture_data('webhook_event'))
        ->assertUnauthorized();

    Event::assertNotDispatched(WebhookReceived::class);
});
