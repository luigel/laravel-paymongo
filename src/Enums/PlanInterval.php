<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Billing intervals for subscription plans.
 */
enum PlanInterval: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';
}
