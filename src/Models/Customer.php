<?php

namespace Luigel\Paymongo\Models;

use Illuminate\Support\Collection;
use Luigel\Paymongo\Paymongo;

class Customer extends BaseModel
{
    public function update(array $payload): BaseModel
    {
        return (new Paymongo)->customer()->updateCustomer($this, $payload);
    }

    public function delete(): BaseModel
    {
        return (new Paymongo)->customer()->deleteCustomer($this);
    }

    public function paymentMethods(): BaseModel|Collection
    {
        return (new Paymongo)->customer()->getPaymentMethods($this);
    }
}
