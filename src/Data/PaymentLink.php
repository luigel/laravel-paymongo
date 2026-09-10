<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Carbon\CarbonImmutable;
use Luigel\Paymongo\Enums\PaymentLinkStatus;
use Luigel\Paymongo\Support\Money;
use Throwable;

/**
 * A payment link from the newer `/payment_links` API.
 *
 * That API returns flat objects (`{"data": {...}}` with the id and fields
 * directly on `data`, no `{id, type, attributes}` triple) and ISO 8601
 * string timestamps, so this DTO does not extend {@see Resource}; the full
 * payload stays available through {@see attribute()} and {@see $raw}.
 *
 * For the legacy `/links` API see {@see Link}.
 */
final readonly class PaymentLink
{
    /**
     * @param array<string, mixed>|null $metadata
     * @param array<string, mixed>|null $restrictions
     * @param array<string, mixed>      $raw          The full flat `data` object.
     */
    public function __construct(
        public ?string $id = null,
        public ?int $amount = null,
        public ?string $currency = null,
        public ?string $description = null,
        public ?string $remarks = null,
        public ?PaymentLinkStatus $status = null,
        public ?bool $livemode = null,
        public ?string $url = null,
        public ?string $referenceNumber = null,
        public ?array $metadata = null,
        public ?array $restrictions = null,
        public ?CarbonImmutable $createdAt = null,
        public ?CarbonImmutable $updatedAt = null,
        public array $raw = [],
    ) {
    }

    /**
     * Build the DTO from the flat `data` object of a `/payment_links`
     * response. Tolerant: every missing or unexpectedly typed field maps
     * to null.
     *
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $raw */
        $raw = $data;

        $status = $raw['status'] ?? null;
        $livemode = $raw['livemode'] ?? null;

        return new self(
            id: self::stringOf($raw, 'id'),
            amount: self::intOf($raw, 'amount'),
            currency: self::stringOf($raw, 'currency'),
            description: self::stringOf($raw, 'description'),
            remarks: self::stringOf($raw, 'remarks'),
            status: is_string($status) ? PaymentLinkStatus::tryFrom($status) : null,
            livemode: is_bool($livemode) ? $livemode : null,
            url: self::stringOf($raw, 'url'),
            referenceNumber: self::stringOf($raw, 'reference_number'),
            metadata: self::arrayOf($raw, 'metadata'),
            restrictions: self::arrayOf($raw, 'restrictions'),
            createdAt: self::isoTime($raw, 'created_at'),
            updatedAt: self::isoTime($raw, 'updated_at'),
            raw: $raw,
        );
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }

    /**
     * Read a raw field using dot notation, e.g. `attribute('restrictions.completed_sessions')`.
     */
    public function attribute(string $key, mixed $default = null): mixed
    {
        return data_get($this->raw, $key, $default);
    }

    /**
     * @param array<string, mixed> $raw
     */
    private static function stringOf(array $raw, string $key): ?string
    {
        $value = $raw[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $raw
     */
    private static function intOf(array $raw, string $key): ?int
    {
        $value = $raw[$key] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $raw
     *
     * @return array<string, mixed>|null
     */
    private static function arrayOf(array $raw, string $key): ?array
    {
        $value = $raw[$key] ?? null;

        if (!is_array($value)) {
            return null;
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * Parse an ISO 8601 string timestamp, e.g. `2024-09-09T00:00:00.000Z`.
     *
     * @param array<string, mixed> $raw
     */
    private static function isoTime(array $raw, string $key): ?CarbonImmutable
    {
        $value = $raw[$key] ?? null;

        if (!is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
