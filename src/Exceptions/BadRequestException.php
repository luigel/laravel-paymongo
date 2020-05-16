<?php

namespace Luigel\Paymongo\Exceptions;

use Exception;
use Throwable;

class BadRequestException extends Exception
{
    public function __construct(
        $message = 'The request was not understood, often caused by missing parameters.',
        $code = 400,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
