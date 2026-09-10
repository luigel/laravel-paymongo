<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Enums\WebhookStatus;

final class Webhook extends Resource
{
    /**
     * @param array<string, mixed> $attributes
     * @param string|null          $secretKey  The endpoint's signing secret, used to verify inbound events.
     * @param list<string>         $events
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly ?string $url = null,
        public readonly ?WebhookStatus $status = null,
        public readonly ?string $secretKey = null,
        public readonly bool $livemode = false,
        public readonly array $events = [],
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
            url: self::stringValue($attributes, 'url'),
            status: self::enumValue($attributes, 'status', WebhookStatus::class),
            secretKey: self::stringValue($attributes, 'secret_key'),
            livemode: self::boolValue($attributes, 'livemode'),
            events: self::stringListValue($attributes, 'events'),
        );
    }
}
