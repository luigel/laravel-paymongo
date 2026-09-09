<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Lifecycle states of a payment intent.
 */
enum PaymentIntentStatus: string
{
    case AwaitingPaymentMethod = 'awaiting_payment_method';
    case AwaitingNextAction = 'awaiting_next_action';
    case AwaitingCapture = 'awaiting_capture';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Cancelled = 'cancelled';
}
