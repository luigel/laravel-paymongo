<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Event names a webhook endpoint can subscribe to.
 */
enum WebhookEventType: string
{
    case CheckoutSessionPaymentPaid = 'checkout_session.payment.paid';
    case SourceChargeable = 'source.chargeable';
    case PaymentPaid = 'payment.paid';
    case PaymentFailed = 'payment.failed';
    case PaymentRefunded = 'payment.refunded';
    case PaymentRefundUpdated = 'payment.refund.updated';
    case SubscriptionPastDue = 'subscription.past_due';
    case SubscriptionUnpaid = 'subscription.unpaid';
    case SubscriptionUpdated = 'subscription.updated';
    case SubscriptionInvoiceCreated = 'subscription.invoice.created';
    case SubscriptionInvoiceFinalized = 'subscription.invoice.finalized';
    case SubscriptionInvoicePaid = 'subscription.invoice.paid';
    case SubscriptionInvoicePaymentFailed = 'subscription.invoice.payment_failed';
    case LinkPaymentPaid = 'link.payment.paid';
    case QrphExpired = 'qrph.expired';
}
