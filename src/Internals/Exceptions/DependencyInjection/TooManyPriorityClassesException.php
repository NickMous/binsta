<?php

namespace NickMous\Binsta\Internals\Exceptions\DependencyInjection;

class TooManyPriorityClassesException extends \Exception
{
    public function __construct(string $class, int $count)
    {
        parent::__construct("Class '$class' has too many priority classes defined: $count. Please ensure only one priority class is set.");
    }
}
