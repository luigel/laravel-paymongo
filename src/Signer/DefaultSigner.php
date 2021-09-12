<?php

namespace Luigel\Paymongo\Signer;

class DefaultSigner implements Signer
{
    /**
     * Calculate signature.
     *
     * @param  string|int  $timestamp
     * @param  string  $contentBody
     * @param  string  $secret
     * @return string
     */
    public function calculateSignature($timestamp, string $contentBody, string $secret): string
    {
        return hash_hmac('sha256', $timestamp.'.'.$contentBody, $secret);
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
