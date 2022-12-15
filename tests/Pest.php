<?php

use Luigel\Paymongo\Models\Link;
use Luigel\Paymongo\Models\Token;
use Luigel\Paymongo\Models\Source;
use Luigel\Paymongo\Models\Payment;
use Luigel\Paymongo\Traits\Request;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Tests\BaseTestCase;
use Luigel\Paymongo\Models\PaymentIntent;
use Luigel\Paymongo\Models\PaymentMethod;

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

function createToken(): Token
{
    return Paymongo::token()->create([
        'number' => getTestCardWithout3dSecure(),
        'exp_month' => 12,
        'exp_year' => 25,
        'cvc' => '123',
        'billing' => [
            'address' => [
                'line1' => 'Test Address',
                'city' => 'Cebu City',
                'postal_code' => '6000',
                'country' => 'PH',
            ],
            'name' => 'Rigel Kent Carbonel',
            'email' => 'rigel20.kent@gmail.com',
            'phone' => '928392893',
        ],
    ]);
}

function createPaymentIntent(): PaymentIntent
{
    return Paymongo::paymentIntent()->create([
        'amount' => 100,
        'payment_method_allowed' => [
            'card', 'paymaya',
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

function createPaymentMethod(): PaymentMethod
{
    return Paymongo::paymentMethod()
        ->create([
            'type' => 'card',
            'details' => [
                'card_number' => getTestCardWithout3dSecure(),
                'exp_month' => 12,
                'exp_year' => 25,
                'cvc' => '123',
            ],
            'billing' => [
                'address' => [
                    'line1' => 'Somewhere there',
                    'city' => 'Cebu City',
                    'state' => 'Cebu',
                    'country' => 'PH',
                    'postal_code' => '6000',
                ],
                'name' => 'Rigel Kent Carbonel',
                'email' => 'rigel20.kent@gmail.com',
                'phone' => '0935454875545',
            ],
        ]);
}

function createSource($type = 'gcash'): Source
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

function createPayment(Source|Token $source): Payment
{
    return Paymongo::payment()
        ->create([
            'amount' => 100.00,
            'currency' => 'PHP',
            'description' => 'Testing payment',
            'statement_descriptor' => 'Test Paymongo',
            'source' => [
                'id' => $source->id,
                'type' => $source->type,
            ],
        ]);
}

function createCardPayment(): Payment
{
    $paymentIntent = createPaymentIntent();
    $paymentMethod = createPaymentMethod();
    $attachedPaymentIntent = $paymentIntent->attach($paymentMethod->id, 'http://example.com/success');
    $cardPayment = new Payment();
    $cardPayment = $cardPayment->setData($attachedPaymentIntent->payments[0]);

    return $cardPayment;
}

function createLink(): Link
{
    return Paymongo::link()->create([
        'amount' => 100.00,
        'description' => 'Link Test',
        'remarks' => 'laravel-paymongo'
    ]);
}

function createRequest(
    $method,
    $content,
    $uri = '/test',
    $server = ['CONTENT_TYPE' => 'application/json'],
    $parameters = [],
    $cookies = [],
    $files = []
) {
    $request = new \Illuminate\Http\Request();

    return $request->createFromBase(\Symfony\Component\HttpFoundation\Request::create($uri, $method, $parameters, $cookies, $files, $server, $content));
}
