<?php

namespace Luigel\LaravelPaymongo;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Luigel\LaravelPaymongo\Skeleton\SkeletonClass
 */
class LaravelPaymongoFacade extends Facade
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
