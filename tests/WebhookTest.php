<?php

namespace Luigel\Paymongo\Tests;

use Illuminate\Support\Collection;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Webhook;

class WebhookTest extends BaseTestCase
{
    /** @test */
    public function it_can_list_all_webhooks()
    {
        $webhooks = Paymongo::webhook()->all();

        $this->assertInstanceOf(Collection::class, $webhooks);

        $this->assertNotEmpty($webhooks);
    }

    /** @test */
    public function it_can_retrieve_webhook()
    {
        $webhooks = Paymongo::webhook()->all();

        $webhook = Paymongo::webhook()->find($webhooks[0]->getId());

        $this->assertInstanceOf(Webhook::class, $webhook);

        $this->assertEquals($webhooks[0]->getId(), $webhook->getId());
    }
}
