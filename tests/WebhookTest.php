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

    // /** @test */
    // public function it_can_create_a_webhook()
    // {
    //     $webhooks = Paymongo::webhook()->all();
    //     $webhook = Paymongo::webhook()->create([
    //         'url' => 'http://localhost/webhook/' . $webhooks->count(),
    //         'events' => [
    //             Webhook::SOURCE_CHARGEABLE
    //         ]
    //     ]);

    //     $this->assertInstanceOf(Webhook::class, $webhook);

    //     $this->assertEquals('enabled', $webhook->status);

    //     $this->assertEquals('http://localhost/webhook/' . $webhooks->count(), $webhook->getUrl());

    //     $this->assertInstanceOf(Collection::class, $webhook->getEvents());

    //     $this->assertEquals('source.chargeable', $webhook->getEvents(0));
    // }

    // /** @test */
    // public function it_can_disable_and_enable_webhook()
    // {
    //     $webhooks = Paymongo::webhook()->all();

    //     $webhook = Paymongo::webhook()->find($webhooks[0]->getId());

    //     if ($webhook->status === 'enabled')
    //     {
    //         $webhook->disable();
    //         $webhook = Paymongo::webhook()->find($webhooks[0]->getId());
    //         $this->assertEquals('disabled', $webhook->status);
    //     }
    //     else
    //     {
    //         $webhook->enable();
    //         $webhook = Paymongo::webhook()->find($webhooks[0]->getId());
    //         $this->assertEquals('enabled', $webhook->status);
    //     }
    // }

    /** @test */
    // public function it_can_update_webhook()
    // {
    //     $this->assertTrue(true);
    //     $webhooks = Paymongo::webhook()->all();

    //     $webhook = Paymongo::webhook()->find($webhooks[0]->getId());

    //     if ($webhook->getUrl() === 'http://localhost/updated-webhook-test-1')
    //     {
    //         $webhook = Paymongo::webhook()->find($webhooks[0]->getId());
    //         $webhook = $webhook->update([
    //             'url' => 'http://localhost/updated-webhook-test-2'
    //         ]);
    //         $this->assertEquals('http://localhost/updated-webhook-test-2', $webhook->getUrl());

    //     }
    //     else
    //     {
    //         $webhook = Paymongo::webhook()->find($webhooks[0]->getId());

    //         $webhook = $webhook->update([
    //             'url' => 'http://localhost/updated-webhook-test-1'
    //         ]);
    //         $this->assertEquals('http://localhost/updated-webhook-test-1', $webhook->getUrl());
    //     }

    // }
}
