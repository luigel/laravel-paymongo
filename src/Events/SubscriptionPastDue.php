<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `subscription.past_due` webhook events.
 */
final class SubscriptionPastDue extends WebhookReceived {}
