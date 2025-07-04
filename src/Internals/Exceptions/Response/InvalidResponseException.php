<?php

namespace NickMous\Binsta\Internals\Exceptions\Response;

use Exception;

class InvalidResponseException extends Exception
{
    public function __construct(string $message = 'Invalid response provided.')
    {
        parent::__construct($message);
    }
}
