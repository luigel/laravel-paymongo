<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `subscription.invoice.finalized` webhook events.
 */
final class SubscriptionInvoiceFinalized extends WebhookReceived {}
