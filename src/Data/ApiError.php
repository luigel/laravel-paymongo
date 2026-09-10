<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

final readonly class ApiError
{
    public function __construct(
        public ?string $code = null,
        public ?string $detail = null,
        public ?string $pointer = null,
        public ?string $attribute = null,
    ) {}

    /**
     * Create an error from a single PayMongo `errors[]` entry.
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $source = isset($data['source']) && is_array($data['source']) ? $data['source'] : [];

        $code = $data['code'] ?? null;
        $detail = $data['detail'] ?? null;
        $pointer = $source['pointer'] ?? null;
        $attribute = $source['attribute'] ?? null;

        return new self(
            code: is_string($code) ? $code : null,
            detail: is_string($detail) ? $detail : null,
            pointer: is_string($pointer) ? $pointer : null,
            attribute: is_string($attribute) ? $attribute : null,
        );
    }
}
