<?php

declare(strict_types=1);

namespace Luigel\Paymongo;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;

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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/paymongo.php' => config_path('paymongo.php'),
            ], 'paymongo-config');
        }
    }
}
