<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Webhooks;

use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\Events\CheckoutSessionPaymentPaid;
use Luigel\Paymongo\Events\LinkPaymentPaid;
use Luigel\Paymongo\Events\PaymentFailed;
use Luigel\Paymongo\Events\PaymentIntentAwaitingPaymentMethod;
use Luigel\Paymongo\Events\PaymentIntentSucceeded;
use Luigel\Paymongo\Events\PaymentPaid;
use Luigel\Paymongo\Events\PaymentRefunded;
use Luigel\Paymongo\Events\PaymentRefundUpdated;
use Luigel\Paymongo\Events\QrphExpired;
use Luigel\Paymongo\Events\RefundSucceeded;
use Luigel\Paymongo\Events\SourceChargeable;
use Luigel\Paymongo\Events\SubscriptionActivated;
use Luigel\Paymongo\Events\SubscriptionInvoicePaid;
use Luigel\Paymongo\Events\SubscriptionInvoicePaymentFailed;
use Luigel\Paymongo\Events\SubscriptionPastDue;
use Luigel\Paymongo\Events\SubscriptionUnpaid;
use Luigel\Paymongo\Events\SubscriptionUpdated;
use Luigel\Paymongo\Events\WebhookReceived;

/**
 * Maps dotted webhook event names onto their typed event classes.
 *
 * Event names without an entry (and unknown future names) still dispatch
 * the generic {@see WebhookReceived} event.
 */
final class EventMap
{
    /**
     * @var array<string, class-string<WebhookReceived>>
     */
    public const MAP = [
        WebhookEventType::CheckoutSessionPaymentPaid->value => CheckoutSessionPaymentPaid::class,
        WebhookEventType::SourceChargeable->value => SourceChargeable::class,
        WebhookEventType::PaymentPaid->value => PaymentPaid::class,
        WebhookEventType::PaymentFailed->value => PaymentFailed::class,
        WebhookEventType::PaymentRefunded->value => PaymentRefunded::class,
        WebhookEventType::PaymentRefundUpdated->value => PaymentRefundUpdated::class,
        WebhookEventType::PaymentIntentSucceeded->value => PaymentIntentSucceeded::class,
        WebhookEventType::PaymentIntentAwaitingPaymentMethod->value => PaymentIntentAwaitingPaymentMethod::class,
        WebhookEventType::SubscriptionActivated->value => SubscriptionActivated::class,
        WebhookEventType::SubscriptionPastDue->value => SubscriptionPastDue::class,
        WebhookEventType::SubscriptionUnpaid->value => SubscriptionUnpaid::class,
        WebhookEventType::SubscriptionUpdated->value => SubscriptionUpdated::class,
        WebhookEventType::SubscriptionInvoicePaid->value => SubscriptionInvoicePaid::class,
        WebhookEventType::SubscriptionInvoicePaymentFailed->value => SubscriptionInvoicePaymentFailed::class,
        WebhookEventType::LinkPaymentPaid->value => LinkPaymentPaid::class,
        WebhookEventType::QrphExpired->value => QrphExpired::class,
        WebhookEventType::RefundSucceeded->value => RefundSucceeded::class,
    ];

    /**
     * @return class-string<WebhookReceived>|null
     */
    public static function eventClassFor(string $type): ?string
    {
        return self::MAP[$type] ?? null;
    }
}
