<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `payment.refunded` webhook events.
 */
final class PaymentRefunded extends WebhookReceived {}
