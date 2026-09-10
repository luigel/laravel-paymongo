<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\LinkStatus;
use Luigel\Paymongo\Support\Money;

final class Link extends Resource
{
    /**
     * @param array<string, mixed> $attributes
     * @param list<Payment>        $payments
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?int $amount = null,
        public readonly bool $archived = false,
        public readonly ?Currency $currency = null,
        public readonly ?string $description = null,
        public readonly bool $livemode = false,
        public readonly ?int $fee = null,
        public readonly ?string $checkoutUrl = null,
        public readonly ?string $referenceNumber = null,
        public readonly ?string $remarks = null,
        public readonly ?LinkStatus $status = null,
        public readonly array $payments = [],
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
            archived: self::boolValue($attributes, 'archived'),
            currency: self::enumValue($attributes, 'currency', Currency::class),
            description: self::stringValue($attributes, 'description'),
            livemode: self::boolValue($attributes, 'livemode'),
            fee: self::intValue($attributes, 'fee'),
            checkoutUrl: self::stringValue($attributes, 'checkout_url'),
            referenceNumber: self::stringValue($attributes, 'reference_number'),
            remarks: self::stringValue($attributes, 'remarks'),
            status: self::enumValue($attributes, 'status', LinkStatus::class),
            payments: self::mapPayments($attributes),
        );
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }

    /**
     * PayMongo wraps each payment of a link in its own `data` envelope;
     * bare payment resources are accepted as well.
     *
     * @param array<string, mixed> $attributes
     *
     * @return list<Payment>
     */
    private static function mapPayments(array $attributes): array
    {
        $raw = $attributes['payments'] ?? null;

        if (!is_array($raw)) {
            return [];
        }

        $payments = [];

        foreach ($raw as $payment) {
            if (!is_array($payment)) {
                continue;
            }

            if (isset($payment['data']) && is_array($payment['data'])) {
                $payment = $payment['data'];
            }

            $payments[] = Payment::fromArray($payment);
        }

        return $payments;
    }
}
