<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `checkout_session.payment.paid` webhook events.
 */
final class CheckoutSessionPaymentPaid extends WebhookReceived {}
