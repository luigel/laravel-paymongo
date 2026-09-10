<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PayoutStatus;
use Luigel\Paymongo\Support\Money;

final class Payout extends Resource
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?int $netAmount = null,
        public readonly ?int $fee = null,
        public readonly ?int $taxAmount = null,
        public readonly ?int $refundAmount = null,
        public readonly ?int $disputeAmount = null,
        public readonly ?int $adjustmentAmount = null,
        public readonly ?Currency $currency = null,
        public readonly ?PayoutStatus $status = null,
        public readonly ?string $bankAccountName = null,
        public readonly ?string $bankAccountNumber = null,
        public readonly ?string $bankName = null,
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
            netAmount: self::intValue($attributes, 'net_amount'),
            fee: self::intValue($attributes, 'fee'),
            taxAmount: self::intValue($attributes, 'tax_amount'),
            refundAmount: self::intValue($attributes, 'refund_amount'),
            disputeAmount: self::intValue($attributes, 'dispute_amount'),
            adjustmentAmount: self::intValue($attributes, 'adjustment_amount'),
            currency: self::enumValue($attributes, 'currency', Currency::class),
            status: self::enumValue($attributes, 'status', PayoutStatus::class),
            bankAccountName: self::stringValue($attributes, 'bank_account_name'),
            bankAccountNumber: self::stringValue($attributes, 'bank_account_number'),
            bankName: self::stringValue($attributes, 'bank_name'),
            livemode: self::boolValue($attributes, 'livemode'),
        );
    }

    /**
     * The net amount actually deposited, falling back to the gross amount.
     */
    public function money(): ?Money
    {
        $centavos = $this->netAmount ?? $this->amount;

        return $centavos === null ? null : Money::ofCentavos($centavos);
    }
}
