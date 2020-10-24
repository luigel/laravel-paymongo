<?php

namespace Luigel\Paymongo\Exceptions;

use Exception;
use Throwable;

class MethodNotFoundException extends Exception
{
    public function __construct(
        $message = 'Method not found',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
