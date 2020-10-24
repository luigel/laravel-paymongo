<?php

namespace Luigel\Paymongo;

use Illuminate\Support\ServiceProvider;
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
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'paymongo');

        $this->app->singleton('paymongo', function () {
            return new Paymongo;
        });

        $this->app->bind(Signer::class, config('paymongo.signer'));

        $this->app['router']->aliasMiddleware('paymongo.signature', PaymongoValidateSignature::class);
    }
}
