<?php

declare(strict_types=1);

use Luigel\Paymongo\Client\ClientConfig;

it('builds from the paymongo config array shape', function () {
    $config = ClientConfig::fromArray([
        'secret_key' => 'sk_test_abc',
        'public_key' => 'pk_test_abc',
        'base_url'   => 'https://api.example.test/v1',
        'http'       => [
            'timeout'     => 10,
            'retries'     => 5,
            'retry_delay' => 50,
        ],
        'idempotency' => [
            'auto' => false,
        ],
    ]);

    expect($config->secretKey)->toBe('sk_test_abc')
        ->and($config->publicKey)->toBe('pk_test_abc')
        ->and($config->baseUrl)->toBe('https://api.example.test/v1')
        ->and($config->timeout)->toBe(10)
        ->and($config->retries)->toBe(5)
        ->and($config->retryDelay)->toBe(50)
        ->and($config->autoIdempotency)->toBeFalse();
});

it('applies defaults for missing keys', function () {
    $config = ClientConfig::fromArray(['secret_key' => 'sk_test_abc']);

    expect($config->secretKey)->toBe('sk_test_abc')
        ->and($config->publicKey)->toBeNull()
        ->and($config->baseUrl)->toBe('https://api.paymongo.com/v1')
        ->and($config->timeout)->toBe(30)
        ->and($config->retries)->toBe(2)
        ->and($config->retryDelay)->toBe(200)
        ->and($config->autoIdempotency)->toBeTrue();
});

it('falls back to an empty secret key when unset', function () {
    expect(ClientConfig::fromArray([])->secretKey)->toBe('');
});

it('treats null and empty public keys as null', function () {
    expect(ClientConfig::fromArray(['secret_key' => 'sk', 'public_key' => null])->publicKey)->toBeNull()
        ->and(ClientConfig::fromArray(['secret_key' => 'sk', 'public_key' => ''])->publicKey)->toBeNull();
});

it('clones with a new secret key', function () {
    $config = ClientConfig::fromArray([
        'secret_key'  => 'sk_test_abc',
        'public_key'  => 'pk_test_abc',
        'http'        => ['timeout' => 10, 'retries' => 5, 'retry_delay' => 50],
        'idempotency' => ['auto' => false],
    ]);

    $clone = $config->withSecretKey('sk_live_xyz');

    expect($clone)->not->toBe($config)
        ->and($clone->secretKey)->toBe('sk_live_xyz')
        ->and($clone->publicKey)->toBe('pk_test_abc')
        ->and($clone->baseUrl)->toBe($config->baseUrl)
        ->and($clone->timeout)->toBe(10)
        ->and($clone->retries)->toBe(5)
        ->and($clone->retryDelay)->toBe(50)
        ->and($clone->autoIdempotency)->toBeFalse()
        ->and($config->secretKey)->toBe('sk_test_abc');
});

it('strips the trailing version segment from the base URL for the origin', function () {
    expect((new ClientConfig(secretKey: 'sk'))->origin())->toBe('https://api.paymongo.com')
        ->and((new ClientConfig(secretKey: 'sk', baseUrl: 'https://api.paymongo.com/v1/'))->origin())->toBe('https://api.paymongo.com')
        ->and((new ClientConfig(secretKey: 'sk', baseUrl: 'https://api.example.test/v12'))->origin())->toBe('https://api.example.test')
        ->and((new ClientConfig(secretKey: 'sk', baseUrl: 'http://localhost:8080/proxy/v1'))->origin())->toBe('http://localhost:8080/proxy');
});

it('keeps a base URL without a version segment as the origin', function () {
    expect((new ClientConfig(secretKey: 'sk', baseUrl: 'https://proxy.test/paymongo'))->origin())->toBe('https://proxy.test/paymongo')
        ->and((new ClientConfig(secretKey: 'sk', baseUrl: 'https://proxy.test/version1'))->origin())->toBe('https://proxy.test/version1');
});

it('uses constructor defaults', function () {
    $config = new ClientConfig(secretKey: 'sk_test_abc');

    expect($config->publicKey)->toBeNull()
        ->and($config->baseUrl)->toBe('https://api.paymongo.com/v1')
        ->and($config->timeout)->toBe(30)
        ->and($config->retries)->toBe(2)
        ->and($config->retryDelay)->toBe(200)
        ->and($config->autoIdempotency)->toBeTrue();
});
