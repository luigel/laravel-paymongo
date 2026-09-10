<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Enums\DefaultDevice;

final class Customer extends Resource
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?DefaultDevice $defaultDevice = null,
        public readonly ?string $defaultPaymentMethodId = null,
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
            firstName: self::stringValue($attributes, 'first_name'),
            lastName: self::stringValue($attributes, 'last_name'),
            email: self::stringValue($attributes, 'email'),
            phone: self::stringValue($attributes, 'phone'),
            defaultDevice: self::enumValue($attributes, 'default_device', DefaultDevice::class),
            defaultPaymentMethodId: self::stringValue($attributes, 'default_payment_method_id'),
            livemode: self::boolValue($attributes, 'livemode'),
        );
    }
}
