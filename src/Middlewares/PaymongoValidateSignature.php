<?php

namespace Luigel\Paymongo\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Luigel\Paymongo\Signer\Signer;

class PaymongoValidateSignature
{
    /**
     * Handle an incoming request.
     *
     * @throws \Illuminate\Routing\Exceptions\InvalidSignatureException
     */
    public function handle(Request $request, Closure $next, string $event = null): Response|null
    {
        $payload = $this->headerPayload($request);

        if ($payload) {
            $signature = $this->signature($request, $payload['t'], $event);
            $key = config('paymongo.livemode') ? 'li' : 'te';

            if ($signature == $payload[$key]) {
                return $next($request);
            }
        }
        throw new InvalidSignatureException();
    }

    /**
     * Get header signature payload.
     */
    public function headerPayload(Request $request): array
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
     */
    public function signature(Request $request, string|int $timestamp, string $event = null): string
    {
        return app(Signer::class)->calculateSignature(
            $timestamp,
            $request->getContent(),
            config('paymongo.webhook_signatures.'.$event, config('paymongo.webhook_signature'))
        );
    }
}
