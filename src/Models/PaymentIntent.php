<?php

namespace Luigel\Paymongo\Models;

use Luigel\Paymongo\Paymongo;

class PaymentIntent extends BaseModel
{
    public function cancel()
    {
        return (new Paymongo)->paymentIntent()->cancel($this);
    }

    /**
     * @param  string  $paymentMethodId
     * @param  string|null  $returnUrl
     * @return \Luigel\Paymongo\Models\BaseModel
     */
    public function attach($paymentMethodId, $returnUrl = null)
    {
        return (new Paymongo)->paymentIntent()->attach($this, $paymentMethodId, $returnUrl);
    }
}
