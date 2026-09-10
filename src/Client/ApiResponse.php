<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Client;

final readonly class ApiResponse
{
    /**
     * @param array<array-key, mixed> $body
     */
    public function __construct(
        public array $body,
        public int $status,
    ) {
    }

    /**
     * The `data` payload: a single resource array or a list of resource arrays.
     *
     * @return array<array-key, mixed>
     */
    public function data(): array
    {
        $data = $this->body['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    public function isList(): bool
    {
        return array_is_list($this->data());
    }

    public function hasMore(): bool
    {
        return (bool) ($this->body['has_more'] ?? false);
    }
}
