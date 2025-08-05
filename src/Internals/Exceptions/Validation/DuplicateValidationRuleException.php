<?php

namespace NickMous\Binsta\Internals\Exceptions\Validation;

class DuplicateValidationRuleException extends \Exception
{
    public function __construct(string $ruleName)
    {
        parent::__construct("Duplicate validation rule: {$ruleName}", 400);
    }
}
