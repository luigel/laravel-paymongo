<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `payment.paid` webhook events.
 */
final class PaymentPaid extends WebhookReceived {}
