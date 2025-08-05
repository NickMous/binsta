<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ValidationRule;

class StringRule implements ValidationRule
{
    public const string KEY = 'string';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function validate(mixed $value): bool
    {
        // Check if the value is a string
        if (!is_string($value)) {
            return false;
        }

        // Check if the string is not empty
        return trim($value) !== '';
    }
}
