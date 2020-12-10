<?php

namespace Luigel\Paymongo\Tests;

use Luigel\Paymongo\Models\BaseModel;

class BaseModelTest extends BaseTestCase
{
    /** @test */
    public function it_can_set_data()
    {
        $model = new BaseModel();
        $data = [
            'id' => 'pay_4exEiTS1V6f2V28zo33S5v4R',
            'type' => 'payment',
            'attributes' => [
                'access_url' => null,
                'amount' => 10000,
                'balance_transaction_id' => 'bal_txn_2BVC9Xe4Hu9egYfoEvRcQYXS',
                'billing' => [
                    'address' => [
                        'city' => 'Cebu City',
                        'country' => 'PH',
                        'line1' => 'Test Address',
                        'line2' => null,
                        'postal_code' => '6000',
                        'state' => null,
                    ],
                    'email' => 'rigel20.kent@gmail.com',
                    'name' => 'Rigel Kent Carbonel',
                    'phone' => '928392893',
                ],
                'currency' => 'PHP',
                'description' => 'Testing payment',
                'disputed' => false,
                'external_reference_number' => null,
                'fee' => 1850,
                'foreign_fee' => 100,
                'livemode' => false,
                'net_amount' => 8050,
                'payout' => null,
                'source' => [
                    'id' => 'tok_ayYr6JnADwLnmA4bGvmg4Hzx',
                    'type' => 'token',
                    'brand' => 'visa',
                    'country' => 'US',
                    'last4' => '4345',
                ],
                'statement_descriptor' => 'Test Paymongo',
                'status' => 'paid',
                'refunds' => [],
                'available_at' => 1604653200,
                'created_at' => 1604210517,
                'paid_at' => 1604210517,
                'updated_at' => 1604210517,
            ],
        ];
        $baseModel = $model->setData($data);

        $this->assertNotEmpty($baseModel->getData());
        $this->assertEquals('pay_4exEiTS1V6f2V28zo33S5v4R', $baseModel->id);
        $this->assertArrayHasKey('address', $baseModel->billing);
        $this->assertEquals('pay_4exEiTS1V6f2V28zo33S5v4R', $baseModel->getId());
        $this->assertArrayHasKey('address', $baseModel->getBilling());
        $this->assertEquals(100.00, $baseModel->getAmount());
        $this->assertEquals(100.00, $baseModel->amount);
        $this->assertEquals([
            'city' => 'Cebu City',
            'country' => 'PH',
            'line1' => 'Test Address',
            'line2' => null,
            'postal_code' => '6000',
            'state' => null,
        ], $baseModel->getBillingAddress());
    }
}
