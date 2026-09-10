<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `payment.failed` webhook events.
 */
final class PaymentFailed extends WebhookReceived
{
}
