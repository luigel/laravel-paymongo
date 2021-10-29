<?php

namespace Luigel\Paymongo\Signer;

class DefaultSigner implements Signer
{
    /**
     * Calculate signature.
     */
    public function calculateSignature(string|int $timestamp, string $contentBody, string $secret): string
    {
        return hash_hmac('sha256', $timestamp.'.'.$contentBody, $secret);
    }

    /**
     * Get signature header name.
     */
    public function signatureHeaderName(): string
    {
        return config('paymongo.signature_header_name');
    }
}
