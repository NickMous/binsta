<?php

namespace NickMous\Binsta\Internals\Exceptions\DependencyInjection;

use Exception;
use ReflectionParameter;

class NoTypesException extends Exception
{
    public function __construct(string $class, ReflectionParameter $parameter)
    {
        parent::__construct("No types found for parameter '$parameter->name' in class '$class'. Please ensure the parameter has a type hint or is documented with @param.");
    }
}
