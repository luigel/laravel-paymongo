<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `subscription.invoice.paid` webhook events.
 */
final class SubscriptionInvoicePaid extends WebhookReceived {}
