<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ParameterizedValidationRule;
use NickMous\Binsta\Internals\Validation\ValidationRule;
use RedBeanPHP\R;

class UniqueRule implements ValidationRule, ParameterizedValidationRule
{
    private string $table = '';
    private string $field = '';

    public function getKey(): string
    {
        return 'unique';
    }

    public function setParameters(array $parameters): void
    {
        $this->table = $parameters[0] ?? '';
        $this->field = $parameters[1] ?? '';
    }

    public function validate(mixed $value): bool
    {
        // Only validate strings
        if (!is_string($value)) {
            return false;
        }

        // Trim whitespace
        $value = trim($value);

        // Check if value is empty after trimming
        if (empty($value)) {
            return false;
        }

        // Check if table and field are configured
        if (empty($this->table) || empty($this->field)) {
            return false;
        }

        // Check if value already exists in the specified table/field
        $existing = R::findOne($this->table, "{$this->field} = ?", [$value]);

        return $existing === null;
    }
}
