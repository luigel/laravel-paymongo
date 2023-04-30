<?php

namespace Luigel\Paymongo\Models;

use Luigel\Paymongo\Paymongo;

class Checkout extends BaseModel
{
    public function expire()
    {
        return (new Paymongo)->checkout()->expireCheckout($this);
    }
}
