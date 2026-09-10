<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `link.payment.paid` webhook events.
 */
final class LinkPaymentPaid extends WebhookReceived {}
