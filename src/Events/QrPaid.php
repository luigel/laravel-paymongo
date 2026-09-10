<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `qr.paid` webhook events.
 */
final class QrPaid extends WebhookReceived {}
