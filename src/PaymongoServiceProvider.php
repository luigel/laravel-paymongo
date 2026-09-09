<?php

declare(strict_types=1);

namespace Luigel\Paymongo;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Luigel\Paymongo\Commands\WebhookCreateCommand;
use Luigel\Paymongo\Commands\WebhookListCommand;
use Luigel\Paymongo\Commands\WebhookToggleCommand;
use Luigel\Paymongo\Http\Controllers\WebhookController;
use Luigel\Paymongo\Http\Middleware\VerifyWebhookSignature;

final class PaymongoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/paymongo.php', 'paymongo');

        $this->app->singleton(PaymongoManager::class, function (Application $app): PaymongoManager {
            $config = $app->make(Repository::class)->get('paymongo', []);

            return new PaymongoManager(
                $app->make(Factory::class),
                is_array($config) ? $config : [],
            );
        });

        $this->app->alias(PaymongoManager::class, 'paymongo');
    }

    public function boot(): void
    {
        $this->app->make(Router::class)->aliasMiddleware('paymongo.signature', VerifyWebhookSignature::class);

        $csrfMiddleware = self::csrfMiddleware();

        Router::macro('paymongoWebhooks', function (string $uri = 'paymongo/webhook', ?string $secret = null) use ($csrfMiddleware): Route {
            /** @var Router $this */
            return $this->post($uri, WebhookController::class)
                ->middleware($secret === null ? 'paymongo.signature' : "paymongo.signature:{$secret}")
                ->withoutMiddleware($csrfMiddleware)
                ->name('paymongo.webhooks');
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/paymongo.php' => config_path('paymongo.php'),
            ], 'paymongo-config');

            $this->commands([
                WebhookCreateCommand::class,
                WebhookListCommand::class,
                WebhookToggleCommand::class,
            ]);
        }
    }

    /**
     * The framework's CSRF middleware, under every class name that exists.
     *
     * Laravel 11 and 12 register Illuminate\Foundation\Http\Middleware\ValidateCsrfToken
     * in the "web" group; Laravel 13 renamed it to PreventRequestForgery and kept the
     * old names as deprecated subclasses. Route middleware exclusion matches on the
     * exact class name, so a subclass name never excludes the parent - every existing
     * name has to be listed for the exclusion to hold across versions.
     *
     * @return list<string>
     */
    private static function csrfMiddleware(): array
    {
        return array_values(array_filter([
            'Illuminate\Foundation\Http\Middleware\PreventRequestForgery',
            'Illuminate\Foundation\Http\Middleware\ValidateCsrfToken',
            'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken',
        ], static fn (string $class): bool => class_exists($class)));
    }
}
