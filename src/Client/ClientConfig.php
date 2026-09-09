<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Client;

final readonly class ClientConfig
{
    public function __construct(
        public string $secretKey,
        public ?string $publicKey = null,
        public string $baseUrl = 'https://api.paymongo.com/v1',
        public int $timeout = 30,
        public int $retries = 2,
        public int $retryDelay = 200,
        public bool $autoIdempotency = true,
    ) {}

    /**
     * Build a config from the `config/paymongo.php` array shape.
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        $http = isset($config['http']) && is_array($config['http']) ? $config['http'] : [];
        $idempotency = isset($config['idempotency']) && is_array($config['idempotency']) ? $config['idempotency'] : [];

        $secretKey = $config['secret_key'] ?? null;
        $publicKey = $config['public_key'] ?? null;
        $baseUrl = $config['base_url'] ?? null;

        return new self(
            secretKey: is_string($secretKey) ? $secretKey : '',
            publicKey: is_string($publicKey) && $publicKey !== '' ? $publicKey : null,
            baseUrl: is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : 'https://api.paymongo.com/v1',
            timeout: (int) ($http['timeout'] ?? 30),
            retries: (int) ($http['retries'] ?? 2),
            retryDelay: (int) ($http['retry_delay'] ?? 200),
            autoIdempotency: (bool) ($idempotency['auto'] ?? true),
        );
    }

    public function withSecretKey(string $secretKey): self
    {
        return new self(
            secretKey: $secretKey,
            publicKey: $this->publicKey,
            baseUrl: $this->baseUrl,
            timeout: $this->timeout,
            retries: $this->retries,
            retryDelay: $this->retryDelay,
            autoIdempotency: $this->autoIdempotency,
        );
    }
}
