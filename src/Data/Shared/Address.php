<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data\Shared;

final readonly class Address
{
    public function __construct(
        public ?string $line1 = null,
        public ?string $line2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postalCode = null,
        public ?string $country = null,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $line1 = $data['line1'] ?? null;
        $line2 = $data['line2'] ?? null;
        $city = $data['city'] ?? null;
        $state = $data['state'] ?? null;
        $postalCode = $data['postal_code'] ?? null;
        $country = $data['country'] ?? null;

        return new self(
            line1: is_string($line1) ? $line1 : null,
            line2: is_string($line2) ? $line2 : null,
            city: is_string($city) ? $city : null,
            state: is_string($state) ? $state : null,
            postalCode: is_string($postalCode) ? $postalCode : null,
            country: is_string($country) ? $country : null,
        );
    }
}
