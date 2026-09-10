<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data\Shared;

/**
 * The step required to continue a payment intent, e.g. a `redirect`
 * to an e-wallet or 3DS authorization page.
 */
final readonly class NextAction
{
    public function __construct(
        public ?string $type = null,
        public ?string $url = null,
        public ?string $returnUrl = null,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $type = $data['type'] ?? null;
        $redirect = $data['redirect'] ?? null;
        $url = is_array($redirect) ? ($redirect['url'] ?? null) : null;
        $returnUrl = is_array($redirect) ? ($redirect['return_url'] ?? null) : null;

        return new self(
            type: is_string($type) ? $type : null,
            url: is_string($url) ? $url : null,
            returnUrl: is_string($returnUrl) ? $returnUrl : null,
        );
    }
}
