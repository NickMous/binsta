<?php

namespace NickMous\Binsta\Internals\Validation;

interface ParameterizedValidationRule extends ValidationRule
{
    /**
     * @param array<int, string> $parameters
     */
    public function setParameters(array $parameters): void;
}
