<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;

/**
 * Base class for the real-API contract tests in tests/Contract.
 *
 * Unlike the default TestCase it allows real HTTP requests and reads the
 * PayMongo keys from the actual environment instead of the fake test keys.
 */
abstract class ContractTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Contract tests talk to the real PayMongo test API.
        Http::allowStrayRequests();
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('paymongo.secret_key', env('PAYMONGO_SECRET_KEY'));
        $app['config']->set('paymongo.public_key', env('PAYMONGO_PUBLIC_KEY'));
        $app['config']->set('paymongo.webhooks.secret', env('PAYMONGO_WEBHOOK_SECRET'));
    }
}
