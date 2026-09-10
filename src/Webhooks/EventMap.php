<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Webhooks;

use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\Events\CheckoutSessionPaymentPaid;
use Luigel\Paymongo\Events\DisputeCreated;
use Luigel\Paymongo\Events\DisputeResolved;
use Luigel\Paymongo\Events\LinkPaymentPaid;
use Luigel\Paymongo\Events\PaymentFailed;
use Luigel\Paymongo\Events\PaymentIntentAwaitingPaymentMethod;
use Luigel\Paymongo\Events\PaymentIntentSucceeded;
use Luigel\Paymongo\Events\PaymentPaid;
use Luigel\Paymongo\Events\PaymentRefunded;
use Luigel\Paymongo\Events\PaymentRefundUpdated;
use Luigel\Paymongo\Events\PayoutDeposited;
use Luigel\Paymongo\Events\PayoutReturned;
use Luigel\Paymongo\Events\QrExpired;
use Luigel\Paymongo\Events\QrPaid;
use Luigel\Paymongo\Events\QrphExpired;
use Luigel\Paymongo\Events\RefundSucceeded;
use Luigel\Paymongo\Events\SourceChargeable;
use Luigel\Paymongo\Events\SubscriptionActivated;
use Luigel\Paymongo\Events\SubscriptionInvoiceCreated;
use Luigel\Paymongo\Events\SubscriptionInvoiceFinalized;
use Luigel\Paymongo\Events\SubscriptionInvoicePaid;
use Luigel\Paymongo\Events\SubscriptionInvoicePaymentFailed;
use Luigel\Paymongo\Events\SubscriptionPastDue;
use Luigel\Paymongo\Events\SubscriptionUnpaid;
use Luigel\Paymongo\Events\SubscriptionUpdated;
use Luigel\Paymongo\Events\WebhookReceived;

/**
 * Maps dotted webhook event names onto their typed event classes.
 *
 * Every {@see WebhookEventType} case has an entry; unknown future names
 * still dispatch only the generic {@see WebhookReceived} event.
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
        WebhookEventType::SubscriptionInvoiceCreated->value => SubscriptionInvoiceCreated::class,
        WebhookEventType::SubscriptionInvoiceFinalized->value => SubscriptionInvoiceFinalized::class,
        WebhookEventType::SubscriptionInvoicePaid->value => SubscriptionInvoicePaid::class,
        WebhookEventType::SubscriptionInvoicePaymentFailed->value => SubscriptionInvoicePaymentFailed::class,
        WebhookEventType::LinkPaymentPaid->value => LinkPaymentPaid::class,
        WebhookEventType::QrphExpired->value => QrphExpired::class,
        WebhookEventType::QrPaid->value => QrPaid::class,
        WebhookEventType::QrExpired->value => QrExpired::class,
        WebhookEventType::RefundSucceeded->value => RefundSucceeded::class,
        WebhookEventType::DisputeCreated->value => DisputeCreated::class,
        WebhookEventType::DisputeResolved->value => DisputeResolved::class,
        WebhookEventType::PayoutDeposited->value => PayoutDeposited::class,
        WebhookEventType::PayoutReturned->value => PayoutReturned::class,
    ];

    /**
     * @return class-string<WebhookReceived>|null
     */
    public static function eventClassFor(string $type): ?string
    {
        return self::MAP[$type] ?? null;
    }
}
