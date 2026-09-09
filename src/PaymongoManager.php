<?php

declare(strict_types=1);

namespace Luigel\Paymongo;

use Illuminate\Http\Client\Factory;
use Luigel\Paymongo\Client\ClientConfig;
use Luigel\Paymongo\Client\PaymongoClient;

final class PaymongoManager
{
    private ?PaymongoClient $client = null;

    /**
     * @param  array<string, mixed>  $config  The `paymongo` config array.
     */
    public function __construct(
        private readonly Factory $http,
        private array $config,
    ) {}

    public function client(): PaymongoClient
    {
        return $this->client ??= new PaymongoClient($this->http, ClientConfig::fromArray($this->config));
    }

    /**
     * Clone the manager with a different secret key (multi-account use).
     */
    public function withSecretKey(string $secretKey): self
    {
        $manager = clone $this;
        $manager->config['secret_key'] = $secretKey;
        $manager->flushMemoized();

        return $manager;
    }

    /**
     * Forget memoized instances so they are rebuilt from the current config.
     * Later phases clear memoized service instances here as well.
     */
    private function flushMemoized(): void
    {
        $this->client = null;
    }
}
