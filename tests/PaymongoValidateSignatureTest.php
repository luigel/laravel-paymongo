<?php

namespace Luigel\Paymongo\Tests;

use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Luigel\Paymongo\Middlewares\PaymongoValidateSignature;

class PaymongoValidateSignatureTest extends BaseTestCase
{
    /** @test */
    public function it_will_not_allow_invalid_signature()
    {
        $this->expectException(InvalidSignatureException::class);

        $request = new Request();

        $middleware = new PaymongoValidateSignature();

        $middleware->handle($request, function () {
        });
    }

    /** @test */
    public function it_will_allow_valid_signature()
    {
        config(['paymongo.webhook_signature' => 'testing']);

        $data = [
            'test' => 'this is a test',
        ];

        $jsonPayload = json_encode($data);
        $request = $this->createRequest('post', $jsonPayload);

        $timestamp = (string) now()->timestamp;
        $teSignature = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), 'testing');

        $request->headers->set('paymongo-signature', 't='.$timestamp.',te='.$teSignature.',li=');

        $middleware = new PaymongoValidateSignature();

        $middleware->handle($request, function ($req) use ($jsonPayload) {
            $this->assertEquals($req->getContent(), $jsonPayload);
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
