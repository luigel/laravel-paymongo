<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PlanInterval;
use Luigel\Paymongo\Support\Money;

final class Plan extends Resource
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?Currency $currency = null,
        public readonly ?string $description = null,
        public readonly ?PlanInterval $interval = null,
        public readonly ?int $intervalCount = null,
        public readonly ?string $name = null,
        public readonly ?string $planType = null,
        public readonly ?int $cycleCount = null,
        public readonly bool $livemode = false,
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
            amount: self::intValue($attributes, 'amount'),
            currency: self::enumValue($attributes, 'currency', Currency::class),
            description: self::stringValue($attributes, 'description'),
            interval: self::enumValue($attributes, 'interval', PlanInterval::class),
            intervalCount: self::intValue($attributes, 'interval_count'),
            name: self::stringValue($attributes, 'name'),
            planType: self::stringValue($attributes, 'plan_type'),
            cycleCount: self::intValue($attributes, 'cycle_count'),
            livemode: self::boolValue($attributes, 'livemode'),
        );
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }
}
