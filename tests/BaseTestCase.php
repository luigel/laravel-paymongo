<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\PaymongoServiceProvider;
use Orchestra\Testbench\TestCase;

class BaseTestCase extends TestCase
{
    protected const TEST_VISA_CARD_WITHOUT_3D_SECURE = '4343434343434345';
    protected const TEST_VISA_CARD_WITHOUT_3D_SECURE_LAST_4 = '4345';

    protected function getPackageProviders($app)
    {
        return [PaymongoServiceProvider::class];
    }
}
