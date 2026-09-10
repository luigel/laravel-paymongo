<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\PaymongoServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            PaymongoServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('paymongo.secret_key', 'sk_test_fake');
        $app['config']->set('paymongo.public_key', 'pk_test_fake');
        $app['config']->set('paymongo.webhooks.secret', 'whsk_test_fake');
    }
}
