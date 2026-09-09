<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data\Shared;

final readonly class Billing
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?Address $address = null,
    ) {}

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        $address = $data['address'] ?? null;

        return new self(
            name: is_string($name) ? $name : null,
            email: is_string($email) ? $email : null,
            phone: is_string($phone) ? $phone : null,
            address: is_array($address) ? Address::fromArray($address) : null,
        );
    }
}
