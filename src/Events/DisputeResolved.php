<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `dispute.resolved` webhook events.
 */
final class DisputeResolved extends WebhookReceived {}
