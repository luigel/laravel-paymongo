<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Carbon\CarbonImmutable;
use Luigel\Paymongo\Enums\CancellationReason;
use Luigel\Paymongo\Enums\SubscriptionStatus;

final class Subscription extends Resource
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  Plan|null  $plan  The full plan resource nested in the payload.
     * @param  array<string, mixed>|null  $latestInvoice
     * @param  array<string, mixed>|null  $setupIntent
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?string $customerId = null,
        public readonly ?string $planId = null,
        public readonly ?Plan $plan = null,
        public readonly ?string $paymentMethodId = null,
        public readonly ?SubscriptionStatus $status = null,
        public readonly ?CarbonImmutable $anchorDate = null,
        public readonly ?CarbonImmutable $nextBillingSchedule = null,
        public readonly ?CarbonImmutable $cancelledAt = null,
        public readonly ?CancellationReason $cancellationReason = null,
        public readonly ?array $latestInvoice = null,
        public readonly ?array $setupIntent = null,
        public readonly bool $livemode = false,
    ) {
        parent::__construct($id, $type, $attributes);
    }

    public static function fromArray(array $data): self
    {
        [$id, $type, $attributes] = self::parseResource($data);

        $plan = self::arrayValue($attributes, 'plan');

        return new self(
            id: $id,
            type: $type,
            attributes: $attributes,
            customerId: self::stringValue($attributes, 'customer_id'),
            planId: self::stringValue($attributes, 'plan_id'),
            plan: $plan === null ? null : Plan::fromArray($plan),
            paymentMethodId: self::stringValue($attributes, 'payment_method_id'),
            status: self::enumValue($attributes, 'status', SubscriptionStatus::class),
            anchorDate: self::timestampValue($attributes, 'anchor_date'),
            nextBillingSchedule: self::timestampValue($attributes, 'next_billing_schedule'),
            cancelledAt: self::timestampValue($attributes, 'cancelled_at'),
            cancellationReason: self::enumValue($attributes, 'cancellation_reason', CancellationReason::class),
            latestInvoice: self::arrayValue($attributes, 'latest_invoice'),
            setupIntent: self::arrayValue($attributes, 'setup_intent'),
            livemode: self::boolValue($attributes, 'livemode'),
        );
    }

    /**
     * Convert a unix-seconds attribute into an immutable date, when present.
     *
     * @param  array<string, mixed>  $attributes
     */
    private static function timestampValue(array $attributes, string $key): ?CarbonImmutable
    {
        $value = $attributes[$key] ?? null;

        return is_int($value) ? CarbonImmutable::createFromTimestamp($value) : null;
    }
}
