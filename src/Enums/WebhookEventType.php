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
    case PaymentIntentSucceeded = 'payment_intent.succeeded';
    case PaymentIntentAwaitingPaymentMethod = 'payment_intent.awaiting_payment_method';
    case SubscriptionActivated = 'subscription.activated';
    case SubscriptionPastDue = 'subscription.past_due';
    case SubscriptionUnpaid = 'subscription.unpaid';
    case SubscriptionUpdated = 'subscription.updated';
    case SubscriptionInvoiceCreated = 'subscription.invoice.created';
    case SubscriptionInvoiceFinalized = 'subscription.invoice.finalized';
    case SubscriptionInvoicePaid = 'subscription.invoice.paid';
    case SubscriptionInvoicePaymentFailed = 'subscription.invoice.payment_failed';
    case LinkPaymentPaid = 'link.payment.paid';
    case QrphExpired = 'qrph.expired';
    case QrPaid = 'qr.paid';
    case QrExpired = 'qr.expired';
    case RefundSucceeded = 'refund.succeeded';
    case DisputeCreated = 'dispute.created';
    case DisputeResolved = 'dispute.resolved';
    case PayoutDeposited = 'payout.deposited';
    case PayoutReturned = 'payout.returned';
}
