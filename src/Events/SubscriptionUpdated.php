<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `subscription.updated` webhook events.
 */
final class SubscriptionUpdated extends WebhookReceived {}
