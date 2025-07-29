<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ValidationRule;

class RequiredRule implements ValidationRule
{
    public const string KEY = 'required';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function validate(mixed $value): bool
    {
        // Check if the value is null or an empty string
        return !(is_null($value) || (is_string($value) && trim($value) === ''));
    }
}
