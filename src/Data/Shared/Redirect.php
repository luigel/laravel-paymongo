<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data\Shared;

/**
 * Redirect URLs of a source: where the customer authorizes the payment
 * and where PayMongo sends them afterwards.
 */
final readonly class Redirect
{
    public function __construct(
        public ?string $success = null,
        public ?string $failed = null,
        public ?string $checkoutUrl = null,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $success = $data['success'] ?? null;
        $failed = $data['failed'] ?? null;
        $checkoutUrl = $data['checkout_url'] ?? null;

        return new self(
            success: is_string($success) ? $success : null,
            failed: is_string($failed) ? $failed : null,
            checkoutUrl: is_string($checkoutUrl) ? $checkoutUrl : null,
        );
    }
}
