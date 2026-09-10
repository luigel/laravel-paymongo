<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `dispute.created` webhook events.
 */
final class DisputeCreated extends WebhookReceived {}
