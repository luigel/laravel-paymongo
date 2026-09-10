<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Data\Shared\Billing;
use Luigel\Paymongo\Enums\PaymentMethodType;

final class PaymentMethod extends Resource
{
    /**
     * @param array<string, mixed>      $attributes
     * @param PaymentMethodType|null    $methodType The `type` attribute (card, gcash, ...); named
     *                                              methodType because $type holds the resource type.
     * @param array<string, mixed>|null $details
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?PaymentMethodType $methodType = null,
        public readonly bool $livemode = false,
        public readonly ?Billing $billing = null,
        public readonly ?array $details = null,
        public readonly ?array $metadata = null,
    ) {
        parent::__construct($id, $type, $attributes);
    }

    public static function fromArray(array $data): self
    {
        [$id, $type, $attributes] = self::parseResource($data);

        $billing = self::arrayValue($attributes, 'billing');

        return new self(
            id: $id,
            type: $type,
            attributes: $attributes,
            methodType: self::enumValue($attributes, 'type', PaymentMethodType::class),
            livemode: self::boolValue($attributes, 'livemode'),
            billing: $billing === null ? null : Billing::fromArray($billing),
            details: self::arrayValue($attributes, 'details'),
            metadata: self::arrayValue($attributes, 'metadata'),
        );
    }
}
