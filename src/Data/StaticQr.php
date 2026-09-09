<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

/**
 * A static in-store QR Ph code from the v1 `/qrph/generate` endpoint
 * (resource type `code`).
 */
final class StaticQr extends Resource
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?string $mobileNumber = null,
        public readonly ?string $qrImage = null,
        public readonly ?string $name = null,
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
            mobileNumber: self::stringValue($attributes, 'mobile_number'),
            qrImage: self::stringValue($attributes, 'qr_image'),
            name: self::stringValue($attributes, 'name'),
        );
    }
}
