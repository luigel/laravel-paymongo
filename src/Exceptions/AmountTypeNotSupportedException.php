<?php

namespace Luigel\Paymongo\Exceptions;

use Exception;
use Throwable;

class AmountTypeNotSupportedException extends Exception
{
    public function __construct(
        $message = 'The amount_type used is not supported.',
        $code = 422,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
