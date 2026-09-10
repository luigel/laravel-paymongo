<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

/**
 * A merchant's payout schedule.
 *
 * The schedule kind lives in `attributes.type` (the resource `type` names
 * the resource itself), so it is exposed as {@see $scheduleType}.
 */
final class PayoutSchedule extends Resource
{
    /**
     * @param array<string, mixed>    $attributes
     * @param list<string>            $options
     * @param array<array-key, mixed> $lineup
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?string $scheduleType = null,
        public readonly array $options = [],
        public readonly array $lineup = [],
    ) {
        parent::__construct($id, $type, $attributes);
    }

    public static function fromArray(array $data): self
    {
        [$id, $type, $attributes] = self::parseResource($data);

        return new self(
            id: $id,
            type: $type,
            attributes: $attributes,
            scheduleType: self::stringValue($attributes, 'type'),
            options: self::stringListValue($attributes, 'options'),
            lineup: self::arrayValue($attributes, 'lineup') ?? [],
        );
    }
}
