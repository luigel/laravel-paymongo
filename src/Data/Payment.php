<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Carbon\CarbonImmutable;
use Luigel\Paymongo\Data\Shared\Billing;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PaymentStatus;
use Luigel\Paymongo\Support\Money;

final class Payment extends Resource
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>|null  $source  The `{id, type}` of the charged source.
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?Currency $currency = null,
        public readonly ?PaymentStatus $status = null,
        public readonly ?string $description = null,
        public readonly ?string $statementDescriptor = null,
        public readonly ?int $fee = null,
        public readonly ?int $netAmount = null,
        public readonly bool $livemode = false,
        public readonly ?Billing $billing = null,
        public readonly ?array $source = null,
        public readonly ?string $externalReferenceNumber = null,
        public readonly ?string $paymentIntentId = null,
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
            amount: self::intValue($attributes, 'amount'),
            currency: self::enumValue($attributes, 'currency', Currency::class),
            status: self::enumValue($attributes, 'status', PaymentStatus::class),
            description: self::stringValue($attributes, 'description'),
            statementDescriptor: self::stringValue($attributes, 'statement_descriptor'),
            fee: self::intValue($attributes, 'fee'),
            netAmount: self::intValue($attributes, 'net_amount'),
            livemode: self::boolValue($attributes, 'livemode'),
            billing: $billing === null ? null : Billing::fromArray($billing),
            source: self::arrayValue($attributes, 'source'),
            externalReferenceNumber: self::stringValue($attributes, 'external_reference_number'),
            paymentIntentId: self::stringValue($attributes, 'payment_intent_id'),
            metadata: self::arrayValue($attributes, 'metadata'),
        );
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }

    public function paidAt(): ?CarbonImmutable
    {
        return $this->timestamp('paid_at');
    }
}
