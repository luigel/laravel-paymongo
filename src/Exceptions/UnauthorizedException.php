<?php

namespace Luigel\Paymongo\Exceptions;

use Exception;
use Throwable;

class UnauthorizedException extends Exception
{
    public function __construct(
        $message = 'You are not authenticated. Either the PayMongo API key is not passed or invalid.',
        $code = 401,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
