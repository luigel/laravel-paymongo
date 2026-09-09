<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `subscription.activated` webhook events.
 */
final class SubscriptionActivated extends WebhookReceived {}
