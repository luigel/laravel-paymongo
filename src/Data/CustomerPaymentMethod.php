<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

/**
 * A payment method saved against a customer. The resource `type` is
 * `customer_payment_method`; the underlying payment method is referenced
 * through {@see $paymentMethodId}.
 */
final class CustomerPaymentMethod extends Resource
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>|null  $details
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?string $paymentMethodId = null,
        public readonly ?string $paymentMethodType = null,
        public readonly ?string $sessionType = null,
        public readonly ?array $details = null,
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
            paymentMethodId: self::stringValue($attributes, 'payment_method_id'),
            paymentMethodType: self::stringValue($attributes, 'payment_method_type'),
            sessionType: self::stringValue($attributes, 'session_type'),
            details: self::arrayValue($attributes, 'details'),
            livemode: self::boolValue($attributes, 'livemode'),
        );
    }
}
