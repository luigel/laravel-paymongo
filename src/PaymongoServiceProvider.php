<?php

declare(strict_types=1);

namespace Luigel\Paymongo;

use Illuminate\Support\ServiceProvider;

final class PaymongoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/paymongo.php', 'paymongo');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/paymongo.php' => config_path('paymongo.php'),
            ], 'paymongo-config');
        }
    }
}
