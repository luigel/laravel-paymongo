<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `payment_intent.awaiting_payment_method` webhook events.
 */
final class PaymentIntentAwaitingPaymentMethod extends WebhookReceived
{
}
