<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ValidationRule;

class IntegerRule implements ValidationRule
{
    public const string KEY = 'integer';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function validate(mixed $value): bool
    {
        // Check if the value is an integer or a numeric string that represents an integer
        if (is_int($value)) {
            return true;
        }

        if (is_string($value)) {
            // Check if it's a numeric string that represents an integer
            return ctype_digit($value) || (is_numeric($value) && (int)$value == $value);
        }

        return false;
    }
}
