<?php

namespace Luigel\Paymongo;

use Illuminate\Support\ServiceProvider;
use Luigel\Paymongo\Commands\WebhookAddCommand;
use Luigel\Paymongo\Commands\WebhookListCommand;
use Luigel\Paymongo\Commands\WebhookToggleCommand;
use Luigel\Paymongo\Middlewares\PaymongoValidateSignature;
use Luigel\Paymongo\Signer\Signer;

class PaymongoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('paymongo.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookListCommand::class,
                WebhookAddCommand::class,
                WebhookToggleCommand::class,
            ]);
        }

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'paymongo');

        $this->app->singleton('paymongo', function () {
            return new Paymongo;
        });

        $this->app->bind(Signer::class, config('paymongo.signer'));

        $this->app['router']->aliasMiddleware('paymongo.signature', PaymongoValidateSignature::class);
    }
}
