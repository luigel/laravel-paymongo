<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Carbon\CarbonImmutable;
use Luigel\Paymongo\Enums\QrMode;
use Luigel\Paymongo\Enums\QrStatus;
use Luigel\Paymongo\Enums\QrType;
use Luigel\Paymongo\Support\Money;

/**
 * An MPM QR code from the v3 QR API.
 *
 * The v3 API returns flat objects (`{"data": {...}}` with the id and fields
 * directly on `data`, no `{id, type, attributes}` triple), so this DTO does
 * not extend {@see Resource}; the full payload stays available through
 * {@see attribute()} and {@see $raw}.
 */
final readonly class MpmQr
{
    /**
     * @param array<string, mixed> $raw The full flat `data` object.
     */
    public function __construct(
        public ?string $id = null,
        public ?QrStatus $status = null,
        public ?QrType $type = null,
        public ?QrMode $mode = null,
        public ?string $nation = null,
        public ?string $qrString = null,
        public ?string $qrImage = null,
        public ?int $transactionAmount = null,
        public ?string $transactionCurrency = null,
        public ?string $merchantName = null,
        public ?string $merchantCity = null,
        public ?CarbonImmutable $expiresAt = null,
        public ?CarbonImmutable $createdAt = null,
        public ?CarbonImmutable $updatedAt = null,
        public array $raw = [],
    ) {
    }

    /**
     * Build the DTO from the flat `data` object of a v3 QR response.
     * Tolerant: every missing or unexpectedly typed field maps to null.
     *
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $raw */
        $raw = $data;

        $status = $raw['status'] ?? null;
        $type = $raw['type'] ?? null;
        $mode = $raw['mode'] ?? null;

        return new self(
            id: self::stringOf($raw, 'id'),
            status: is_string($status) ? QrStatus::tryFrom($status) : null,
            type: is_string($type) ? QrType::tryFrom($type) : null,
            mode: is_string($mode) ? QrMode::tryFrom($mode) : null,
            nation: self::stringOf($raw, 'nation'),
            qrString: self::stringOf($raw, 'qr_string'),
            qrImage: self::stringOf($raw, 'qr_image'),
            transactionAmount: self::intOf($raw, 'transaction_amount'),
            transactionCurrency: self::stringOf($raw, 'transaction_currency'),
            merchantName: self::stringOf($raw, 'merchant_name'),
            merchantCity: self::stringOf($raw, 'merchant_city'),
            expiresAt: self::unixTime($raw, 'expires_at'),
            createdAt: self::unixTime($raw, 'created_at'),
            updatedAt: self::unixTime($raw, 'updated_at'),
            raw: $raw,
        );
    }

    /**
     * The transaction amount of a dynamic QR, as money.
     */
    public function money(): ?Money
    {
        return $this->transactionAmount === null ? null : Money::ofCentavos($this->transactionAmount);
    }

    /**
     * Read a raw field using dot notation, e.g. `attribute('metadata.order_id')`.
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
     */
    private static function unixTime(array $raw, string $key): ?CarbonImmutable
    {
        $value = $raw[$key] ?? null;

        return is_int($value) ? CarbonImmutable::createFromTimestamp($value) : null;
    }
}
