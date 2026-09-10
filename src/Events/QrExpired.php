<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Events;

/**
 * Dispatched for `qr.expired` webhook events.
 */
final class QrExpired extends WebhookReceived
{
}
