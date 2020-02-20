<?php

namespace Luigel\LaravelPaymongo\Facades;

use Illuminate\Support\Facades\Facade;
use Luigel\LaravelPaymongo\Paymongo as PaymongoFacade;

/**
 * @see \Luigel\LaravelPaymongo\Skeleton\SkeletonClass
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
        return PaymongoFacade::class;
    }
}
