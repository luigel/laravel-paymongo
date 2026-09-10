<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\Route;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Http\Middleware\VerifyWebhookSignature;
use Luigel\Paymongo\PaymongoManager;

beforeEach(function () {
    if ($reason = e2eSkipReason()) {
        $this->markTestSkipped($reason);
    }
});

it('auto-discovers the package service provider', function () {
    expect(app()->bound('paymongo'))->toBeTrue()
        ->and(app('paymongo'))->toBeInstanceOf(PaymongoManager::class)
        ->and(Paymongo::getFacadeRoot())->toBeInstanceOf(PaymongoManager::class);
});

it('is configured with a sandbox secret key', function () {
    expect(config('paymongo.secret_key'))->toBeString()->toStartWith('sk_test_')
        ->and(config('paymongo.livemode'))->toBeFalsy();
});

it('registers the webhook route through the Router macro', function () {
    $route = Route::getRoutes()->getByName('paymongo.webhooks');

    expect($route)->not->toBeNull();
    expect($route->uri())->toBe('paymongo/webhook')
        ->and($route->methods())->toContain('POST');
});

/**
 * The framework's CSRF middleware has been spelled three different ways across
 * the versions this package supports: Laravel 11 and 12 register
 * Illuminate\Foundation\Http\Middleware\ValidateCsrfToken in the `web` group,
 * Laravel 13 renamed it to PreventRequestForgery and kept ValidateCsrfToken and
 * VerifyCsrfToken as deprecated subclasses. Asserting the absence of one exact
 * class name therefore passes vacuously on the versions that use another, which
 * is precisely how the Laravel 13 breakage slipped through. Match on either
 * spelling instead.
 *
 * @param mixed $middleware
 */
function isCsrfMiddleware($middleware): bool
{
    if (!is_string($middleware)) {
        return false;
    }

    $short = class_basename($middleware);

    return str_contains($short, 'Csrf') || str_contains($short, 'RequestForgery');
}

it('excludes the webhook route from CSRF validation', function () {
    // The console kernel never syncs the middleware groups to the router, so
    // without this the `web` group stays an unexpanded string and every
    // assertion below passes on an empty haystack.
    app(HttpKernel::class);

    $router = app('router');
    $route = Route::getRoutes()->getByName('paymongo.webhooks');

    $onRoute = array_values(array_filter($router->gatherRouteMiddleware($route), 'isCsrfMiddleware'));

    expect($onRoute)->toBe([]);

    // Guard against a vacuous pass: the exclusion above only means something if
    // the `web` group this route belongs to does carry a CSRF middleware.
    $inWebGroup = array_values(array_filter($router->resolveMiddleware(['web']), 'isCsrfMiddleware'));

    expect($inWebGroup)->not->toBe([]);
});

it('aliases the signature middleware', function () {
    expect(app('router')->getMiddleware())
        ->toHaveKey('paymongo.signature', VerifyWebhookSignature::class);
});

it('lists webhooks through the artisan command against the live sandbox', function () {
    $this->artisan('paymongo:webhook:list')->assertExitCode(0);
});
