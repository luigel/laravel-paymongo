<?php

namespace Luigel\Paymongo\Tests;

use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Luigel\Paymongo\Middlewares\PaymongoValidateSignature;

it('will not allow invalid signature', function () {
    $this->expectException(InvalidSignatureException::class);

    $request = new Request();

    $middleware = new PaymongoValidateSignature();

    $middleware->handle($request, function () {
    });
});

it('will allow valid signature', function () {
    config(['paymongo.webhook_signature' => 'testing']);

    $data = [
        'test' => 'this is a test',
    ];

    $jsonPayload = json_encode($data);
    $request = createRequest('post', $jsonPayload);

    $timestamp = (string) now()->timestamp;
    $teSignature = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), 'testing');

    $request->headers->set('paymongo-signature', 't='.$timestamp.',te='.$teSignature.',li=');

    $middleware = new PaymongoValidateSignature();

    $middleware->handle($request, function ($req) use ($jsonPayload) {
        $this->assertEquals($req->getContent(), $jsonPayload);
    });
});
