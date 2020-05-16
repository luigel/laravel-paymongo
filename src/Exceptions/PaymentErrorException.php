<?php

namespace Luigel\Paymongo\Exceptions;

use Exception;
use Throwable;

class PaymentErrorException extends Exception
{
    public function __construct(
        $message = 'There is an error during payment',
        $code = 402,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
