<?php

namespace NickMous\Binsta\Internals\Exceptions;

use Exception;

class InvalidRouteFileGiven extends Exception
{
    public function __construct(string $file)
    {
        parent::__construct("Invalid route file given: {$file}");
    }
}