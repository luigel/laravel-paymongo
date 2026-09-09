<?php

declare(strict_types=1);

use Luigel\Paymongo\Testing\Fixtures;

// List payloads carry a reduced payment shape, so the items stay inline
// instead of delegating to Fixtures::payment().
return Fixtures::list([
    [
        'id' => 'pay_hvTn9EyxduZ9gV8WHhSGYqBi',
        'type' => 'payment',
        'attributes' => [
            'amount' => 150050,
            'billing' => null,
            'currency' => 'PHP',
            'description' => 'Order #10101',
            'external_reference_number' => null,
            'fee' => 5252,
            'livemode' => false,
            'net_amount' => 144798,
            'payment_intent_id' => 'pi_UWL2ZP2rBjMPS9UfnqAROSXg',
            'source' => ['id' => 'card_wjRvHkgtLHMAtaKuQoQGtiPT', 'type' => 'card'],
            'statement_descriptor' => 'LUIGEL STORE',
            'status' => 'paid',
            'metadata' => null,
            'paid_at' => 1725840060,
            'created_at' => 1725840000,
            'updated_at' => 1725840060,
        ],
    ],
    [
        'id' => 'pay_Mw7qLcJk2ZtR5yXbA8sVdN3e',
        'type' => 'payment',
        'attributes' => [
            'amount' => 250000,
            'billing' => null,
            'currency' => 'PHP',
            'description' => 'Order #10102',
            'external_reference_number' => null,
            'fee' => 8750,
            'livemode' => false,
            'net_amount' => 241250,
            'payment_intent_id' => 'pi_WgVh4nUuTmXzKpQrEeYyBbCc',
            'source' => ['id' => 'src_hE2Fx8sBoGrVjqZQY6nDdT4c', 'type' => 'gcash'],
            'statement_descriptor' => 'LUIGEL STORE',
            'status' => 'paid',
            'metadata' => null,
            'paid_at' => 1725926460,
            'created_at' => 1725926400,
            'updated_at' => 1725926460,
        ],
    ],
], hasMore: true);
