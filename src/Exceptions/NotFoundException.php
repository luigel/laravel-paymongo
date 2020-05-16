<?php

namespace Luigel\Paymongo\Exceptions;

use Exception;
use Throwable;

class NotFoundException extends Exception
{
    public function __construct(
        $message = 'Not found record',
        $code = 404,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
