<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Support\Money;

/**
 * A single line item inside a checkout session. Line items have no id of
 * their own, so this is a plain value object rather than a {@see Resource}.
 */
final readonly class LineItem
{
    /**
     * @param list<string> $images
     */
    public function __construct(
        public ?int $amount = null,
        public ?Currency $currency = null,
        public ?string $description = null,
        public array $images = [],
        public ?string $name = null,
        public ?int $quantity = null,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $amount = $data['amount'] ?? null;
        $currency = $data['currency'] ?? null;
        $description = $data['description'] ?? null;
        $name = $data['name'] ?? null;
        $quantity = $data['quantity'] ?? null;

        return new self(
            amount: is_int($amount) ? $amount : null,
            currency: is_string($currency) ? Currency::tryFrom($currency) : null,
            description: is_string($description) ? $description : null,
            images: self::mapImages($data['images'] ?? null),
            name: is_string($name) ? $name : null,
            quantity: is_int($quantity) ? $quantity : null,
        );
    }

    /**
     * The per-unit amount as a money value.
     */
    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }

    /**
     * @return list<string>
     */
    private static function mapImages(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $images = [];

        foreach ($value as $image) {
            if (is_string($image)) {
                $images[] = $image;
            }
        }

        return $images;
    }
}
