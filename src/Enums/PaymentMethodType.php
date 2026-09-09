<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * Payment method kinds accepted by PayMongo.
 */
enum PaymentMethodType: string
{
    case Card = 'card';
    case Gcash = 'gcash';
    case GrabPay = 'grab_pay';
    case Paymaya = 'paymaya';
    case ShopeePay = 'shopee_pay';
    case Qrph = 'qrph';
    case Billease = 'billease';
    case Dob = 'dob';
    case Brankas = 'brankas';
    case Atome = 'atome';
}
