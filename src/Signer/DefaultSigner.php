<?php

namespace Luigel\Paymongo\Signer;

class DefaultSigner implements Signer
{
    /**
     * Calculate signature.
     *
     * @param  string|int $timestamp
     * @param  array  $payload
     * @param  string $secret
     *
     * @return string
     */
    public function calculateSignature($timestamp, array $payload, string $secret): string
    {
        $payloadJson = json_encode($payload);

        return hash_hmac('sha256', $timestamp.'.'.$payloadJson, $secret);
    }

    /**
     * Get signature header name.
     *
     * @return string
     */
    public function signatureHeaderName(): string
    {
        return config('paymongo.signature_header_name');
    }
}
