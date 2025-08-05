<?php

namespace NickMous\Binsta\Internals\Exceptions\DependencyInjection;

use Exception;

class NoPrioritySetException extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("No priority set for class '$class'. Please ensure a priority is defined.");
    }
}
