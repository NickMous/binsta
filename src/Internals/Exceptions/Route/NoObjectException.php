<?php

namespace NickMous\Binsta\Internals\Exceptions\Route;

use Exception;

class NoObjectException extends Exception
{
    public function __construct()
    {
        parent::__construct("Something different than an object was passed as a route.");
    }
}
