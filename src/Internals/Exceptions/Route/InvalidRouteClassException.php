<?php

namespace NickMous\Binsta\Internals\Exceptions\Route;

class InvalidRouteClassException extends \Exception
{
    public function __construct(string $className)
    {
        parent::__construct("Invalid route class: {$className}");
    }
}
