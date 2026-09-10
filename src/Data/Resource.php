<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use BackedEnum;
use Carbon\CarbonImmutable;

/**
 * Base class for every PayMongo API resource.
 *
 * Typed properties on concrete resources cover the documented attributes;
 * the full raw payload always remains available through {@see attribute()}.
 */
abstract class Resource
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly array $attributes,
    ) {}

    /**
     * Build the resource from a raw `{id, type, attributes}` payload.
     *
     * @param  array<array-key, mixed>  $data
     * @return static
     */
    abstract public static function fromArray(array $data): self;

    /**
     * Read a raw attribute using dot notation, e.g. `attribute('billing.address.city')`.
     */
    public function attribute(string $key, mixed $default = null): mixed
    {
        return data_get($this->attributes, $key, $default);
    }

    /**
     * @return array{id: string, type: string, attributes: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'attributes' => $this->attributes,
        ];
    }

    public function createdAt(): ?CarbonImmutable
    {
        return $this->timestamp('created_at');
    }

    public function updatedAt(): ?CarbonImmutable
    {
        return $this->timestamp('updated_at');
    }

    /**
     * Convert a unix-seconds attribute into an immutable date, when present.
     */
    protected function timestamp(string $key): ?CarbonImmutable
    {
        $value = $this->attribute($key);

        return is_int($value) ? CarbonImmutable::createFromTimestamp($value) : null;
    }

    /**
     * Split a raw resource payload into `[id, type, attributes]`.
     *
     * @param  array<array-key, mixed>  $data
     * @return array{string, string, array<string, mixed>}
     */
    protected static function parseResource(array $data): array
    {
        $id = $data['id'] ?? null;
        $type = $data['type'] ?? null;
        $rawAttributes = $data['attributes'] ?? null;

        /** @var array<string, mixed> $attributes */
        $attributes = is_array($rawAttributes) ? $rawAttributes : [];

        return [
            is_string($id) ? $id : '',
            is_string($type) ? $type : '',
            $attributes,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected static function stringValue(array $attributes, string $key): ?string
    {
        $value = $attributes[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected static function intValue(array $attributes, string $key): ?int
    {
        $value = $attributes[$key] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected static function boolValue(array $attributes, string $key): bool
    {
        return (bool) ($attributes[$key] ?? false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>|null
     */
    protected static function arrayValue(array $attributes, string $key): ?array
    {
        $value = $attributes[$key] ?? null;

        if (! is_array($value)) {
            return null;
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return list<string>
     */
    protected static function stringListValue(array $attributes, string $key): array
    {
        $value = $attributes[$key] ?? null;

        if (! is_array($value)) {
            return [];
        }

        $strings = [];

        foreach ($value as $item) {
            if (is_string($item)) {
                $strings[] = $item;
            }
        }

        return $strings;
    }

    /**
     * Map a string attribute onto a backed enum; unknown values become null
     * while the raw value stays available through {@see attribute()}.
     *
     * @template TEnum of BackedEnum
     *
     * @param  array<string, mixed>  $attributes
     * @param  class-string<TEnum>  $enum
     * @return TEnum|null
     */
    protected static function enumValue(array $attributes, string $key, string $enum): ?BackedEnum
    {
        $value = $attributes[$key] ?? null;

        return is_string($value) ? $enum::tryFrom($value) : null;
    }
}
