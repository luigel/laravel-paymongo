<?php

declare(strict_types=1);

use Luigel\Paymongo\Exceptions\InvalidWebhookSignatureException;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Webhooks\SignatureVerifier;

/**
 * Build a `Paymongo-Signature` header signing $payload at $timestamp.
 */
function signature_verifier_header(string $payload, string $secret, int $timestamp, bool $livemode = false): string
{
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return $livemode
        ? "t={$timestamp},te=,li={$signature}"
        : "t={$timestamp},te={$signature},li=";
}

it('accepts a valid test-mode signature', function () {
    $payload = '{"data":{"id":"evt_1"}}';
    $header = signature_verifier_header($payload, 'whsk_test_fake', time());

    expect(fn () => (new SignatureVerifier())->verify($payload, $header, 'whsk_test_fake', false))
        ->not->toThrow(InvalidWebhookSignatureException::class);
});

it('accepts a valid live-mode signature from the li component', function () {
    $payload = '{"data":{"id":"evt_1"}}';
    $header = signature_verifier_header($payload, 'whsk_live_fake', time(), livemode: true);

    expect(fn () => (new SignatureVerifier())->verify($payload, $header, 'whsk_live_fake', true))
        ->not->toThrow(InvalidWebhookSignatureException::class);
});

it('rejects a signature made with the wrong secret', function () {
    $payload = '{"data":{"id":"evt_1"}}';
    $header = signature_verifier_header($payload, 'whsk_wrong_secret', time());

    (new SignatureVerifier())->verify($payload, $header, 'whsk_test_fake', false);
})->throws(InvalidWebhookSignatureException::class, 'signature does not match');

it('rejects a tampered payload', function () {
    $header = signature_verifier_header('{"amount":100}', 'whsk_test_fake', time());

    (new SignatureVerifier())->verify('{"amount":999999}', $header, 'whsk_test_fake', false);
})->throws(InvalidWebhookSignatureException::class, 'signature does not match');

it('rejects a correctly signed request whose timestamp is outside the tolerance', function () {
    $payload = '{"data":{"id":"evt_1"}}';
    $header = signature_verifier_header($payload, 'whsk_test_fake', time() - 400);

    (new SignatureVerifier(300))->verify($payload, $header, 'whsk_test_fake', false);
})->throws(InvalidWebhookSignatureException::class, 'outside the allowed tolerance');

it('skips the timestamp check when tolerance is 0', function () {
    $payload = '{"data":{"id":"evt_1"}}';
    $header = signature_verifier_header($payload, 'whsk_test_fake', time() - 999999);

    expect(fn () => (new SignatureVerifier(0))->verify($payload, $header, 'whsk_test_fake', false))
        ->not->toThrow(InvalidWebhookSignatureException::class);
});

it('rejects a header with no key=value pairs at all', function () {
    (new SignatureVerifier())->verify('{}', 'complete garbage', 'whsk_test_fake', false);
})->throws(InvalidWebhookSignatureException::class, 'missing or non-numeric timestamp');

it('rejects a header with a non-numeric timestamp', function () {
    (new SignatureVerifier())->verify('{}', 't=not-a-number,te=abc123,li=', 'whsk_test_fake', false);
})->throws(InvalidWebhookSignatureException::class, 'missing or non-numeric timestamp');

it('rejects a header missing the signature component for the current mode', function () {
    $timestamp = time();

    // Only the live-mode component is present while verifying in test mode.
    (new SignatureVerifier())->verify('{}', "t={$timestamp},li=abc123", 'whsk_test_fake', false);
})->throws(InvalidWebhookSignatureException::class, 'missing te signature component');

it('rejects a missing header', function () {
    (new SignatureVerifier())->verify('{}', null, 'whsk_test_fake', false);
})->throws(InvalidWebhookSignatureException::class, 'Missing Paymongo-Signature header');

it('throws an exception that is a PaymongoException with no HTTP status', function () {
    try {
        (new SignatureVerifier())->verify('{}', null, 'whsk_test_fake', false);
    } catch (InvalidWebhookSignatureException $exception) {
        expect($exception)->toBeInstanceOf(PaymongoException::class)
            ->and($exception->status)->toBeNull()
            ->and($exception->errors())->toBe([]);

        return;
    }

    $this->fail('Expected an InvalidWebhookSignatureException.');
});
