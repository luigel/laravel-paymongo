<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\PaymongoServiceProvider;
use Orchestra\Testbench\TestCase;

class BaseTest extends TestCase
{
    protected const TEST_VISA_CARD_WITHOUT_3D_SECURE = '4343434343434345';
    protected const TEST_VISA_CARD_WITHOUT_3D_SECURE_LAST_4 = '4345';

    /**
     * Test example to remove warning
     *
     * @test
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    protected function getPackageProviders($app)
    {
        return [PaymongoServiceProvider::class];
    }
}
