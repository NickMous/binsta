<?php

namespace NickMous\Binsta\Internals\Exceptions;

class EntityNotFoundException extends \Exception
{
    public function __construct(string $entityType, string $parameterValue)
    {
        parent::__construct("{$entityType} not found for parameter: {$parameterValue}");
    }
}
