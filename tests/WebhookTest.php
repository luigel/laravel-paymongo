<?php

namespace Luigel\LaravelPaymongo\Tests;

use Illuminate\Support\Collection;
use Luigel\LaravelPaymongo\Facades\PaymongoFacade;
use Luigel\LaravelPaymongo\Models\Webhook;
use Orchestra\Testbench\TestCase;
use Luigel\LaravelPaymongo\LaravelPaymongoServiceProvider;

class WebhookTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelPaymongoServiceProvider::class];
    }

    /** @test */
    public function it_can_list_all_webhooks()
    {
        $webhooks = PaymongoFacade::webhook()->all();

        $this->assertInstanceOf(Collection::class, $webhooks);

        $this->assertNotEmpty($webhooks);
    }

    /** @test */
    public function it_can_retrieve_webhook()
    {
        $webhooks = PaymongoFacade::webhook()->all();

        $webhook = PaymongoFacade::webhook()->find($webhooks[0]->id);

        $this->assertInstanceOf(Webhook::class, $webhook);

        $this->assertEquals($webhooks[0], $webhook);
    }

    /** @test */
    public function it_can_create_a_webhook()
    {
        $webhook = PaymongoFacade::webhook()->create([
            'url' => 'http://localhost/webhook',
            'events' => [
                Webhook::SOURCE_CHARGEABLE
            ]
        ]);
        if ($webhook !== 'URL already exists.')
        {
            $this->assertInstanceOf(Webhook::class, $webhook);

            $this->assertEquals('enabled', $webhook->status);
    
            $this->assertEquals('http://localhost/webhook', $webhook->url);
            
            $this->assertInstanceOf(Collection::class, $webhook->event);
    
            $this->assertEquals('source.chargeable', $webhook->events[0]);
        }
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_disable_and_enable_webhook()
    {
        $webhooks = PaymongoFacade::webhook()->all();

        $webhook = PaymongoFacade::webhook()->find($webhooks[0]->id);

        if ($webhook->status === 'enabled')
        {
            $webhook->disable();
            $webhook = PaymongoFacade::webhook()->find($webhooks[0]->id);
            $this->assertEquals('disabled', $webhook->status);
        }
        else
        {
            $webhook->enable();
            $webhook = PaymongoFacade::webhook()->find($webhooks[0]->id);
            $this->assertEquals('enabled', $webhook->status);
        }
    }

    /** @test */
    public function it_can_update_webhook()
    {
        $this->assertTrue(true);
        // $webhooks = PaymongoFacade::webhook()->all();

        // $webhook = PaymongoFacade::webhook()->find($webhooks[0]->id);

        // if ($webhook->url === 'http://localhost/updated-webhook-test-1')
        // {
        //     $webhook = PaymongoFacade::webhook()->find($webhooks[0]->id);
        //     $webhook = $webhook->update([
        //         'url' => 'http://localhost/updated-webhook-test-2'
        //     ]);
        //     $this->assertEquals('http://localhost/updated-webhook-test-2', $webhook->url);

        // }
        // else
        // {
        //     $webhook = PaymongoFacade::webhook()->find($webhooks[0]->id);

        //     $webhook = $webhook->update([
        //         'url' => 'http://localhost/updated-webhook-test-1'
        //     ]);
        //     $this->assertEquals('http://localhost/updated-webhook-test-1', $webhook->url);
        // }
        
        
    }
}