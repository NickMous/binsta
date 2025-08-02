<?php

namespace NickMous\Binsta\Internals\Exceptions\DependencyInjection;

class DuplicatePrioritySetException extends \Exception
{
    public function __construct(string $class, string $priority)
    {
        parent::__construct("Class '$class' has a duplicate priority set: '$priority'. Please ensure only one priority is defined for this class.");
    }
}
