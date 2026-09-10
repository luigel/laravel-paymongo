<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Carbon\CarbonImmutable;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\RefundReason;
use Luigel\Paymongo\Enums\RefundStatus;
use Luigel\Paymongo\Support\Money;

final class Refund extends Resource
{
    /**
     * @param array<string, mixed>      $attributes
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?Currency $currency = null,
        public readonly bool $livemode = false,
        public readonly ?string $notes = null,
        public readonly ?string $paymentId = null,
        public readonly ?RefundReason $reason = null,
        public readonly ?RefundStatus $status = null,
        public readonly ?array $metadata = null,
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
            livemode: self::boolValue($attributes, 'livemode'),
            notes: self::stringValue($attributes, 'notes'),
            paymentId: self::stringValue($attributes, 'payment_id'),
            reason: self::enumValue($attributes, 'reason', RefundReason::class),
            status: self::enumValue($attributes, 'status', RefundStatus::class),
            metadata: self::arrayValue($attributes, 'metadata'),
        );
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }

    public function refundedAt(): ?CarbonImmutable
    {
        return $this->timestamp('refunded_at');
    }
}
