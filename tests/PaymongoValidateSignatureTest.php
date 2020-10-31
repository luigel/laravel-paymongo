<?php

namespace Luigel\Paymongo\Tests;

use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Luigel\Paymongo\Middlewares\PaymongoValidateSignature;

class PaymongoValidateSignatureTest extends BaseTestCase
{
    public function it_will_not_allow_invalid_signature()
    {
        $this->expectException(InvalidSignatureException::class);

        $request = new Request();

        $middleware = new PaymongoValidateSignature();

        $middleware->handle($request, function () {
        });
    }

    public function it_will_allow_valid_signature()
    {
        config(['paymongo.webhook_signature' => 'testing']);

        $data = [
            'test' => 'this is a test',
        ];

        $jsonPayload = json_encode($data);
        $request = $this->createRequest('post', $jsonPayload);

        $timestamp = now()->timestamp;
        $teSignature = hash_hmac('sha256', 't=' . $timestamp . '.' . $jsonPayload, 'testing');
        $LiSignature = hash_hmac('sha256', 't=' . $timestamp . '.' . $jsonPayload, 'testing');

        $request->headers->set('Paymongo-Signature', 't=' . $timestamp . ',te=' . $teSignature . ',li=' . $LiSignature);
        // dd($teSignature);
        $middleware = new PaymongoValidateSignature();

        $middleware->handle($request, function () {
        });
    }

    protected function createRequest(
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
}
