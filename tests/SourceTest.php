<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\PaymongoServiceProvider;
use Luigel\Paymongo\Models\Source;

class SourceTest extends BaseTest
{
    /** @test */
    public function it_can_create_a_source()
    {
        $source = Paymongo::source()->create([
            'type' => 'gcash',
            'amount' => 100.00,
            'currency' => 'PHP',
            'redirect' => [
                'success' => 'http://localhost/success',
                'failed' => 'http://localhost/failed'
            ]
        ]);

        $this->assertInstanceOf(Source::class, $source);

        $this->assertEquals('source', $source->type);

        $this->assertEquals('gcash', $source->source_type);

        $this->assertEquals('100.00', $source->amount);

        $this->assertEquals([
            'success' => 'http://localhost/success',
            'failed' => 'http://localhost/failed',
            'checkout_url' => $source->redirect['checkout_url']
        ], $source->redirect);
    }
}
