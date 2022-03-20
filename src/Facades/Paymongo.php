<?php

namespace Luigel\Paymongo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Luigel\Paymongo\Paymongo payment()
 * @method static \Luigel\Paymongo\Paymongo paymentIntent()
 * @method static \Luigel\Paymongo\Paymongo source()
 * @method static \Luigel\Paymongo\Paymongo webhook()
 * @method static \Luigel\Paymongo\Paymongo paymentMethod()
 * @method static \Luigel\Paymongo\Paymongo refund()
 * @method static \Luigel\Paymongo\Paymongo token() @deprecated 1.2.0
 * @method static mixed create(array $payload)
 * @method static mixed find(string $id)
 * @method static mixed all()
 * @method static mixed enable()
 * @method static mixed disable()
 */
class Paymongo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paymongo';
    }
}
