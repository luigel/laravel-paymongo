<?php

declare(strict_types=1);

use Luigel\Paymongo\Client\PaymongoClient;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\PaymongoManager;

it('registers the manager as a container singleton aliased to paymongo', function () {
    expect(app('paymongo'))->toBeInstanceOf(PaymongoManager::class)
        ->and(app('paymongo'))->toBe(app(PaymongoManager::class));
});

it('builds the client from the paymongo config', function () {
    $config = app('paymongo')->client()->config();

    expect($config->secretKey)->toBe('sk_test_fake')
        ->and($config->publicKey)->toBe('pk_test_fake')
        ->and($config->baseUrl)->toBe('https://api.paymongo.com/v1')
        ->and($config->timeout)->toBe(30)
        ->and($config->retries)->toBe(2)
        ->and($config->retryDelay)->toBe(200)
        ->and($config->autoIdempotency)->toBeTrue();
});

it('memoizes the client per manager', function () {
    $manager = app('paymongo');

    expect($manager->client())->toBe($manager->client());
});

it('clones the manager with a fresh client when the secret key changes', function () {
    $manager = app('paymongo');
    $other = $manager->withSecretKey('sk_test_other');

    expect($other)->toBeInstanceOf(PaymongoManager::class)
        ->and($other)->not->toBe($manager)
        ->and($other->client()->config()->secretKey)->toBe('sk_test_other')
        ->and($other->client()->config()->publicKey)->toBeNull()
        ->and($other->client())->not->toBe($manager->client())
        ->and($manager->client()->config()->secretKey)->toBe('sk_test_fake');
});

it('uses the public key supplied for another account', function () {
    $other = app('paymongo')->withSecretKey('sk_test_other', publicKey: 'pk_test_other');

    expect($other->client()->config()->secretKey)->toBe('sk_test_other')
        ->and($other->client()->config()->publicKey)->toBe('pk_test_other')
        ->and(app('paymongo')->client()->config()->publicKey)->toBe('pk_test_fake');
});

it('clones the client with a new secret key', function () {
    $client = app('paymongo')->client();
    $other = $client->withSecretKey('sk_test_other');

    expect($other)->not->toBe($client)
        ->and($other->config()->secretKey)->toBe('sk_test_other')
        ->and($other->config()->baseUrl)->toBe($client->config()->baseUrl)
        ->and($client->config()->secretKey)->toBe('sk_test_fake');
});

it('proxies through the Paymongo facade', function () {
    expect(Paymongo::client())->toBeInstanceOf(PaymongoClient::class)
        ->and(Paymongo::client())->toBe(app('paymongo')->client())
        ->and(Paymongo::withSecretKey('sk_test_other')->client()->config()->secretKey)->toBe('sk_test_other');
});
