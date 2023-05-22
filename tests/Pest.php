<?php

use Illuminate\Support\Str;
use Luigel\Paymongo\Models\Link;
use Luigel\Paymongo\Models\Token;
use Luigel\Paymongo\Models\Source;
use Luigel\Paymongo\Models\Payment;
use Luigel\Paymongo\Traits\Request;
use Luigel\Paymongo\Models\Checkout;
use Luigel\Paymongo\Models\Customer;
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
        'remarks' => 'laravel-paymongo',
    ]);
}

function createCustomer(): Customer
{
    return Paymongo::customer()->create([
        'first_name' => 'Gringiemar',
        'last_name' => 'Felix',
        'phone' => '+6391234'.rand(10000, 99999),
        'email' => 'customer'.Str::random(8).rand(0, 100).'@email.com',
        'default_device' => 'phone',
    ]);
}

function createCheckout(): Checkout
{
    return Paymongo::checkout()->create([
        'cancel_url' => 'https://paymongo.rigelkentcarbonel.com/',
        'billing' => [
            'name' => 'Gringiemar Felix',
            'email' => 'gringiemar@felix.com',
            'phone' => '+6391234'.rand(10000, 99999),
        ],
        'description' => 'My checkout session description',
        'line_items' => [
            [
                'amount' => 10000,
                'currency' => 'PHP',
                'description' => 'Something of a product.',
                'images' => [
                    'https://images.unsplash.com/photo-1613243555988-441166d4d6fd?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80'
                ],
                'name' => 'A payment card',
                'quantity' => 1
            ]
        ],
        'payment_method_types' => [
            'atome',
            'billease',
            'card',
            'dob',
            'dob_ubp',
            'gcash',
            'grab_pay', 
            'paymaya'
        ],
        'success_url' => 'https://paymongo.rigelkentcarbonel.com/',
        'statement_descriptor' => 'Laravel Paymongo Library',
        'metadata' => [
            'Key' => 'Value'
        ]
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
