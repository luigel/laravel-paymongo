<?php

declare(strict_types=1);

use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\Events\WebhookReceived;
use Luigel\Paymongo\Webhooks\EventMap;

it('maps every webhook event type onto a typed event class', function () {
    foreach (WebhookEventType::cases() as $case) {
        $class = EventMap::eventClassFor($case->value);

        expect($class)->not->toBeNull("No typed event mapped for [{$case->value}].")
            ->and(is_subclass_of((string) $class, WebhookReceived::class))->toBeTrue(
                "[{$class}] mapped for [{$case->value}] must extend WebhookReceived.",
            );
    }
});

it('maps exactly the known webhook event types, nothing more', function () {
    $known = array_map(
        static fn (WebhookEventType $case): string => $case->value,
        WebhookEventType::cases(),
    );
    $mapped = array_keys(EventMap::MAP);

    sort($known);
    sort($mapped);

    expect($mapped)->toBe($known);
});
