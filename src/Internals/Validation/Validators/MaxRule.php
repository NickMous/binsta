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
        // Handle file uploads - check file size in KB
        if (is_array($value) && isset($value['size'], $value['error'])) {
            if ($value['error'] !== UPLOAD_ERR_OK) {
                return false;
            }
            // Convert bytes to KB and compare
            $fileSizeKB = $value['size'] / 1024;
            return $fileSizeKB <= $this->maxLength;
        }

        // Handle strings - check character length
        if (is_string($value)) {
            return strlen($value) <= $this->maxLength;
        }

        return false;
    }
}
