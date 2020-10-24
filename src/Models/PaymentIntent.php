<?php

namespace Luigel\Paymongo\Models;

use Luigel\Paymongo\Paymongo;

class PaymentIntent extends BaseModel
{
    public function cancel()
    {
        return (new Paymongo)->paymentIntent()->cancel($this);
    }

    public function attach($paymentMethodId)
    {
        return (new Paymongo)->paymentIntent()->attach($this, $paymentMethodId);
    }
}
