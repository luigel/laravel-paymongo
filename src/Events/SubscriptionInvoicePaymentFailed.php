<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `subscription.invoice.payment_failed` webhook events.
 */
final class SubscriptionInvoicePaymentFailed extends WebhookReceived
{
}
