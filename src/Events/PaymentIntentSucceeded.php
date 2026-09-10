<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `payment_intent.succeeded` webhook events.
 */
final class PaymentIntentSucceeded extends WebhookReceived
{
}
