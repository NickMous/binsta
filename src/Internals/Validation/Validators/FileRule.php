<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ValidationRule;

class FileRule implements ValidationRule
{
    public const string KEY = 'file';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function validate(mixed $value): bool
    {
        // Value should be the file array from $_FILES
        if (is_array($value) && isset($value['error'], $value['size'])) {
            return $value['error'] === UPLOAD_ERR_OK;
        }

        return false;
    }
}
