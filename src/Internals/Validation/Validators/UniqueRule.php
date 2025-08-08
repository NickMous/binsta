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

        // Validate table and field names contain only safe characters
        $this->validateIdentifier($this->table);
        $this->validateIdentifier($this->field);
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

    /**
     * Validate that an identifier (table or field name) contains only safe characters
     *
     * @param string $identifier The identifier to validate
     * @throws \InvalidArgumentException If the identifier contains unsafe characters
     */
    private function validateIdentifier(string $identifier): void
    {
        if (empty($identifier)) {
            return;
        }

        // Allow only alphanumeric characters and underscores
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            throw new \InvalidArgumentException(
                "Invalid identifier '{$identifier}'. Only alphanumeric characters and underscores are allowed."
            );
        }
    }
}
