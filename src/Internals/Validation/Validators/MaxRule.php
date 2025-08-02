<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ParameterizedValidationRule;

class MaxRule implements ParameterizedValidationRule
{
    public const string KEY = 'max';

    private int $maxLength = 0;

    public function getKey(): string
    {
        return self::KEY;
    }

    /**
     * @param array<int, string> $parameters
     */
    public function setParameters(array $parameters): void
    {
        if (isset($parameters[0])) {
            $this->maxLength = (int) $parameters[0];
        }
    }

    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return strlen($value) <= $this->maxLength;
    }
}
