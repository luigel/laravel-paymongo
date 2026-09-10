<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Data\Shared\Billing;
use Luigel\Paymongo\Data\Shared\Redirect;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PaymentMethodType;
use Luigel\Paymongo\Support\Money;

/**
 * A source from PayMongo's legacy Sources API (gcash / grab_pay).
 * The API is deprecated by PayMongo; prefer payment intents with
 * e-wallet payment methods.
 */
final class Source extends Resource
{
    /**
     * @param array<string, mixed>   $attributes
     * @param PaymentMethodType|null $sourceType The `type` attribute (gcash, grab_pay); named
     *                                           sourceType because $type holds the resource type.
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?Currency $currency = null,
        public readonly ?PaymentMethodType $sourceType = null,
        public readonly ?string $status = null,
        public readonly ?string $description = null,
        public readonly ?string $statementDescriptor = null,
        public readonly bool $livemode = false,
        public readonly ?Billing $billing = null,
        public readonly ?Redirect $redirect = null,
    ) {
        parent::__construct($id, $type, $attributes);
    }

    public static function fromArray(array $data): self
    {
        [$id, $type, $attributes] = self::parseResource($data);

        $billing = self::arrayValue($attributes, 'billing');
        $redirect = self::arrayValue($attributes, 'redirect');

        return new self(
            id: $id,
            type: $type,
            attributes: $attributes,
            amount: self::intValue($attributes, 'amount'),
            currency: self::enumValue($attributes, 'currency', Currency::class),
            sourceType: self::enumValue($attributes, 'type', PaymentMethodType::class),
            status: self::stringValue($attributes, 'status'),
            description: self::stringValue($attributes, 'description'),
            statementDescriptor: self::stringValue($attributes, 'statement_descriptor'),
            livemode: self::boolValue($attributes, 'livemode'),
            billing: $billing === null ? null : Billing::fromArray($billing),
            redirect: $redirect === null ? null : Redirect::fromArray($redirect),
        );
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }
}
