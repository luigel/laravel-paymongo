<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Data\Shared\NextAction;
use Luigel\Paymongo\Enums\CaptureType;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Support\Money;

final class PaymentIntent extends Resource
{
    /**
     * @param array<string, mixed>      $attributes
     * @param list<string>              $paymentMethodAllowed
     * @param array<string, mixed>|null $paymentMethodOptions
     * @param list<Payment>             $payments
     * @param array<string, mixed>|null $lastPaymentError
     * @param array<string, mixed>|null $setupFutureUsage
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?Currency $currency = null,
        public readonly ?string $description = null,
        public readonly ?string $statementDescriptor = null,
        public readonly ?PaymentIntentStatus $status = null,
        public readonly ?string $clientKey = null,
        public readonly ?CaptureType $captureType = null,
        public readonly bool $livemode = false,
        public readonly array $paymentMethodAllowed = [],
        public readonly ?array $paymentMethodOptions = null,
        public readonly array $payments = [],
        public readonly ?NextAction $nextAction = null,
        public readonly ?array $lastPaymentError = null,
        public readonly ?array $setupFutureUsage = null,
        public readonly ?array $metadata = null,
    ) {
        parent::__construct($id, $type, $attributes);
    }

    public static function fromArray(array $data): self
    {
        [$id, $type, $attributes] = self::parseResource($data);

        $nextAction = self::arrayValue($attributes, 'next_action');

        return new self(
            id: $id,
            type: $type,
            attributes: $attributes,
            amount: self::intValue($attributes, 'amount'),
            currency: self::enumValue($attributes, 'currency', Currency::class),
            description: self::stringValue($attributes, 'description'),
            statementDescriptor: self::stringValue($attributes, 'statement_descriptor'),
            status: self::enumValue($attributes, 'status', PaymentIntentStatus::class),
            clientKey: self::stringValue($attributes, 'client_key'),
            captureType: self::enumValue($attributes, 'capture_type', CaptureType::class),
            livemode: self::boolValue($attributes, 'livemode'),
            paymentMethodAllowed: self::stringListValue($attributes, 'payment_method_allowed'),
            paymentMethodOptions: self::arrayValue($attributes, 'payment_method_options'),
            payments: self::mapPayments($attributes),
            nextAction: $nextAction === null ? null : NextAction::fromArray($nextAction),
            lastPaymentError: self::arrayValue($attributes, 'last_payment_error'),
            setupFutureUsage: self::arrayValue($attributes, 'setup_future_usage'),
            metadata: self::arrayValue($attributes, 'metadata'),
        );
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }

    /**
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
            if (is_array($payment)) {
                $payments[] = Payment::fromArray($payment);
            }
        }

        return $payments;
    }
}
