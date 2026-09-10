<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Support\Money;

/**
 * One transaction lined up in a payout.
 *
 * The resource `type` here is the transaction kind itself (`payment`,
 * `refund`, `dispute`, `adjustment`, `split_payment`, `split_refund`), so
 * {@see Resource::$type} carries it; {@see transactionType()} is a
 * null-safe alias.
 */
final class PayoutTransaction extends Resource
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
        public readonly ?int $netAmount = null,
        public readonly ?int $fee = null,
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
            netAmount: self::intValue($attributes, 'net_amount'),
            fee: self::intValue($attributes, 'fee'),
        );
    }

    /**
     * The transaction kind (`payment`, `refund`, `dispute`, `adjustment`,
     * `split_payment`, `split_refund`), read from the resource type.
     */
    public function transactionType(): ?string
    {
        return $this->type === '' ? null : $this->type;
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }
}
