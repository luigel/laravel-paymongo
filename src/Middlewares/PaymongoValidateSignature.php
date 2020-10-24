<?php

namespace Luigel\Paymongo\Middlewares;

use Closure;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Luigel\Paymongo\Signer\Signer;

class PaymongoValidateSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Routing\Exceptions\InvalidSignatureException
     */
    public function handle($request, Closure $next)
    {
        $payload = $this->headerPayload($request);

        if ($payload) {
            $signature = $this->signature($request, $payload['t']);
            $key = config('paymongo.livemode') ? 'li' : 'te';

            if ($signature == $payload[$key]) {
                return $next($request);
            }
        }

        throw new InvalidSignatureException();
    }

    /**
     * Get header signature payload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function headerPayload($request)
    {
        $payload = $request->header(app(Signer::class)->signatureHeaderName());

        if ($payload === null) {
            return [];
        }

        return collect(explode(',', $payload))
            ->mapWithKeys(function ($val) {
                $pair = explode('=', $val);

                return [$pair[0] => $pair[1]];
            })
            ->filter()
            ->all();
    }

    /**
     * Get webhook signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|int  $timestamp
     * @return string
     */
    public function signature($request, $timestamp)
    {
        return app(Signer::class)->calculateSignature(
            $timestamp,
            $request->getContent(),
            config('paymongo.webhook_signature')
        );
    }
}
