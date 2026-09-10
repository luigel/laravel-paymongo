<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Luigel\Paymongo\Exceptions\InvalidWebhookSignatureException;
use Luigel\Paymongo\Webhooks\SignatureVerifier;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects inbound webhook requests whose Paymongo-Signature header does not
 * match the raw request body. Register it via the `paymongo.signature` alias;
 * pass a name (`paymongo.signature:orders`) to verify against a named secret
 * from `paymongo.webhooks.secrets` instead of the default one.
 */
final class VerifyWebhookSignature
{
    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, ?string $secretName = null): Response
    {
        $verifier = new SignatureVerifier($this->tolerance());

        try {
            $verifier->verify(
                $request->getContent(),
                $request->header('Paymongo-Signature'),
                $this->secret($secretName),
                (bool) config('paymongo.livemode', false),
            );
        } catch (InvalidWebhookSignatureException) {
            abort(401, 'Invalid PayMongo webhook signature.');
        }

        return $next($request);
    }

    private function secret(?string $secretName): string
    {
        $secret = $secretName === null
            ? config('paymongo.webhooks.secret')
            : config("paymongo.webhooks.secrets.{$secretName}");

        if (!is_string($secret) || $secret === '') {
            throw new RuntimeException($secretName === null
                ? 'PayMongo webhook secret is not configured. Set PAYMONGO_WEBHOOK_SECRET (config key paymongo.webhooks.secret).'
                : "PayMongo webhook secret [{$secretName}] is not configured. Set the paymongo.webhooks.secrets.{$secretName} config key.");
        }

        return $secret;
    }

    private function tolerance(): int
    {
        $tolerance = config('paymongo.webhooks.tolerance', 300);

        return is_numeric($tolerance) ? (int) $tolerance : 300;
    }
}
