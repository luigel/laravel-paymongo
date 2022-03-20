<?php

namespace Luigel\Paymongo\Models;

class Refund extends BaseModel
{
    public const REASON_DUPLICATE = 'duplicate';
    public const REASON_FRAUDULENT = 'fraudulent';
    public const REASON_REQUESTED_BY_CUSTOMER = 'requested_by_customer';
    public const REASON_OTHERS = 'others';
}
