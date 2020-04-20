<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\PaymentSource;

class PaymentResourceTest extends BaseTestCase
{
    /** @test */
    public function it_can_create_a_payment_source()
    {
        // create a source first
        $source = Paymongo::source()->create([
            'type' => 'gcash',
            'amount' => 100.00,
            'currency' => 'PHP',
            'redirect' => [
                'success' => 'http://localhost/success',
                'failed' => 'http://localhost/failed'
            ]
        ]);
       
        // Get the Source Id and create a payment resource
        $payment_resource =  Paymongo::payment()->create([
            'amount' => 100.00,
            'currency' => 'PHP',
            'description' => 'This is a test payment',
            'statement_descriptor' => 'Liugel Store',
            'source' => [
                'id' => $source->getId(),
                'type' => 'source'
            ]
        ]);

        $this->assertInstanceOf(PaymentSource::class, $payment_resource);

        $this->assertEquals('gcash', $payment_resource->source_type);

        $this->assertEquals('100.00', $payment_resource->amount);

        $this->assertEquals('2.9', $payment_resource->fee);

    }

    /** @test */
    public function it_can_retrieve_payment_resource()
    {
        // create a source first
        $source = Paymongo::source()->create([
            'type' => 'gcash',
            'amount' => 100.00,
            'currency' => 'PHP',
            'redirect' => [
                'success' => 'http://localhost/success',
                'failed' => 'http://localhost/failed'
            ]
        ]);
       
        // Get the Source Id and create a payment resource
        $payment_resource =  Paymongo::payment()->create([
            'amount' => 100.00,
            'currency' => 'PHP',
            'description' => 'This is a test payment',
            'statement_descriptor' => 'Liugel Store',
            'source' => [
                'id' => $source->getId(),
                'type' => 'source'
            ]
        ]);

        $retrievedPaymentResource = Paymongo::payment()->find($payment_resource->getId());

        $this->assertEquals($payment_resource, $retrievedPaymentResource);
    }
}
