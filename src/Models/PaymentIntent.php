<?php

namespace Luigel\Paymongo\Models;

use Luigel\Paymongo\Paymongo;

class PaymentIntent extends BaseModel
{
    public function cancel(): BaseModel
    {
        return (new Paymongo)->paymentIntent()->cancel($this);
    }

    public function attach(string $paymentMethodId, string|null $returnUrl = null): BaseModel
    {
        return (new Paymongo)->paymentIntent()->attach($this, $paymentMethodId, $returnUrl);
    }
}
