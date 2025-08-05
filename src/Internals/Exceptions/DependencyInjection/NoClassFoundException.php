<?php

namespace NickMous\Binsta\Internals\Exceptions\DependencyInjection;

class NoClassFoundException extends \Exception
{
    public function __construct(string $class)
    {
        parent::__construct("No class found for '$class'. Please ensure the class exists and is autoloaded.");
    }
}
