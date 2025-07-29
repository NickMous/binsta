<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ValidationRule;

class EmailRule implements ValidationRule
{
    public const string KEY = 'email';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function validate(mixed $value): bool
    {
        // Check if the value is a valid email address
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        // Use filter_var to validate the email format
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
