<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `source.chargeable` webhook events.
 */
final class SourceChargeable extends WebhookReceived {}
