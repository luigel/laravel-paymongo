<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Webhooks;

use Luigel\Paymongo\Exceptions\InvalidWebhookSignatureException;

/**
 * Verifies the `Paymongo-Signature: t=<unix>,te=<hex>,li=<hex>` header.
 *
 * The signature is an HMAC-SHA256 over `"{t}.{raw body}"` keyed with the
 * webhook endpoint's secret key; `te` carries the test-mode signature and
 * `li` the live-mode one.
 */
final readonly class SignatureVerifier
{
    /**
     * @param  int  $tolerance  Max allowed clock drift for `t`, in seconds. 0 disables the check.
     */
    public function __construct(private int $tolerance = 300) {}

    /**
     * @throws InvalidWebhookSignatureException
     */
    public function verify(string $payload, ?string $header, string $secret, bool $livemode): void
    {
        if ($header === null || trim($header) === '') {
            throw new InvalidWebhookSignatureException('Missing Paymongo-Signature header.');
        }

        $components = $this->parseHeader($header);

        $timestamp = $components['t'] ?? null;

        if ($timestamp === null || ! ctype_digit($timestamp)) {
            throw new InvalidWebhookSignatureException(
                'Malformed Paymongo-Signature header: missing or non-numeric timestamp (t).'
            );
        }

        $mode = $livemode ? 'li' : 'te';
        $signature = $components[$mode] ?? '';

        if ($signature === '') {
            throw new InvalidWebhookSignatureException(
                "Malformed Paymongo-Signature header: missing {$mode} signature component."
            );
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        if (! hash_equals($expected, $signature)) {
            throw new InvalidWebhookSignatureException('Webhook signature does not match the expected signature.');
        }

        if ($this->tolerance > 0 && abs(time() - (int) $timestamp) > $this->tolerance) {
            throw new InvalidWebhookSignatureException(
                "Webhook timestamp is outside the allowed tolerance of {$this->tolerance} seconds."
            );
        }
    }

    /**
     * Split the header into its `key=value` components.
     *
     * @return array<string, string>
     */
    private function parseHeader(string $header): array
    {
        $components = [];

        foreach (explode(',', $header) as $pair) {
            $pair = trim($pair);

            if ($pair === '' || ! str_contains($pair, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $pair, 2);

            $components[trim($key)] = trim($value);
        }

        return $components;
    }
}
