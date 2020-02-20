<?php

namespace Luigel\LaravelPaymongo\Facades;

use Illuminate\Support\Facades\Facade;
use Luigel\LaravelPaymongo\Paymongo;

/**
 * @see \Luigel\LaravelPaymongo\Skeleton\SkeletonClass
 */
class PaymongoFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Paymongo::class;
    }
}
