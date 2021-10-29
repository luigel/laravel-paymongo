<?php

namespace Luigel\Paymongo\Signer;

interface Signer
{
    /**
     * Get signature header name.
     */
    public function signatureHeaderName(): string;

    /**
     * Calculate signature.
     */
    public function calculateSignature(string|int $timestamp, string $contentBody, string $secret): string;
}
