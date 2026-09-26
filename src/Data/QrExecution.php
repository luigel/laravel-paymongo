<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Support\Money;

/**
 * The result of executing an MPM QR string (v3 QR API): real money movement
 * from the scanner's account. The response only acknowledges the request;
 * PayMongo settles the transfer asynchronously.
 *
 * Flat v3 payload, so this DTO does not extend {@see Resource}; the full
 * payload stays available through {@see attribute()} and {@see $raw}.
 */
final readonly class QrExecution
{
    /**
     * @param  array<string, mixed>  $raw  The full flat `data` object.
     */
    public function __construct(
        public ?string $id = null,
        public ?string $status = null,
        public ?string $referenceNumber = null,
        public ?int $amount = null,
        public array $raw = [],
    ) {}

    /**
     * Build the DTO from the flat `data` object of a v3 QR response.
     * Tolerant: every missing or unexpectedly typed field maps to null.
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $raw */
        $raw = $data;

        $id = $raw['id'] ?? null;
        $status = $raw['status'] ?? null;
        $referenceNumber = $raw['reference_number'] ?? null;
        $amount = $raw['amount'] ?? null;

        return new self(
            id: is_string($id) ? $id : null,
            status: is_string($status) ? $status : null,
            referenceNumber: is_string($referenceNumber) ? $referenceNumber : null,
            amount: is_int($amount) ? $amount : null,
            raw: $raw,
        );
    }

    public function money(): ?Money
    {
        return $this->amount === null ? null : Money::ofCentavos($this->amount);
    }

    /**
     * Read a raw field using dot notation, e.g. `attribute('metadata.order_id')`.
     */
    public function attribute(string $key, mixed $default = null): mixed
    {
        return data_get($this->raw, $key, $default);
    }
}
