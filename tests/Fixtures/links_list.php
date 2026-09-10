<?php

declare(strict_types=1);

use Luigel\Paymongo\Testing\Fixtures;

return Fixtures::list([
    Fixtures::link(),
    [
        'id'         => 'link_Xp4vTn8RkQw2ZyBmCsDe6Fgh',
        'type'       => 'link',
        'attributes' => [
            'amount'           => 250000,
            'archived'         => false,
            'currency'         => 'PHP',
            'description'      => 'Payment for Order #10102',
            'livemode'         => false,
            'fee'              => 8750,
            'remarks'          => null,
            'status'           => 'paid',
            'tax_amount'       => null,
            'taxes'            => [],
            'checkout_url'     => 'https://pm.link/luigel-test/test/Xp4vTn8RkQw2ZyBmCsDe6Fgh',
            'reference_number' => 'AB23CDE',
            'payments'         => [
                [
                    'data' => [
                        'id'         => 'pay_Mw7qLcJk2ZtR5yXbA8sVdN3e',
                        'type'       => 'payment',
                        'attributes' => [
                            'amount'                    => 250000,
                            'billing'                   => null,
                            'currency'                  => 'PHP',
                            'description'               => 'Payment for Order #10102',
                            'external_reference_number' => 'AB23CDE',
                            'fee'                       => 8750,
                            'livemode'                  => false,
                            'net_amount'                => 241250,
                            'payment_intent_id'         => null,
                            'source'                    => ['id' => 'src_hE2Fx8sBoGrVjqZQY6nDdT4c', 'type' => 'gcash'],
                            'statement_descriptor'      => 'LUIGEL STORE',
                            'status'                    => 'paid',
                            'metadata'                  => null,
                            'paid_at'                   => 1725926460,
                            'created_at'                => 1725926400,
                            'updated_at'                => 1725926460,
                        ],
                    ],
                ],
            ],
            'created_at' => 1725926400,
            'updated_at' => 1725926460,
        ],
    ],
], hasMore: true);
