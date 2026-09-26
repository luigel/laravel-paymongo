<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\DisputeStatus;
use Luigel\Paymongo\Support\Money;

/**
 * A dispute (chargeback) a cardholder's bank filed against a payment.
 *
 * PayMongo's API reference does not document the dispute resource, so only
 * the attributes seen on it are typed; read anything else with
 * {@see attribute()}.
 */
final class Dispute extends Resource
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
        public readonly ?DisputeStatus $status = null,
        public readonly ?string $reason = null,
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
            status: self::enumValue($attributes, 'status', DisputeStatus::class),
            reason: self::stringValue($attributes, 'reason'),
        );
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }
}
