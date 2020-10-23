<?php

namespace Luigel\Paymongo\Signer;

interface Signer
{
    /**
     * Get signature header name.
     *
     * @return string
     */
    public function signatureHeaderName(): string;

    /**
     * Calculate signature.
     *
     * @param  string|int $timestamp
     * @param  array  $payload
     * @param  string $secret
     *
     * @return string
     */
    public function calculateSignature($timestamp, array $payload, string $secret): string;
}
