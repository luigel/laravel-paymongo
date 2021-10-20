<?php

use Luigel\Paymongo\Traits\Request;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Tests\BaseTestCase;

uses(BaseTestCase::class, Request::class)
    ->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function getTestCardWithout3dSecure()
{
    return '4343434343434345';
}

function getTestCardLast4()
{
    return '4345';
}


function createPaymentIntent()
{
    return Paymongo::paymentIntent()->create([
        'amount' => 100,
        'payment_method_allowed' => [
            'card', 'paymaya'
        ],
        'payment_method_options' => [
            'card' => [
                'request_three_d_secure' => 'automatic',
            ],
        ],
        'description' => 'This is a test payment intent',
        'statement_descriptor' => 'LUIGEL STORE',
        'currency' => 'PHP',
    ]);
}

function createSource($type = 'gcash')
{
    return Paymongo::source()->create([
        'type' => $type,
        'amount' => 100.00,
        'currency' => 'PHP',
        'redirect' => [
            'success' => 'http://localhost/success',
            'failed' => 'http://localhost/failed',
        ],
    ]);
}
