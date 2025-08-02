<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ParameterizedValidationRule;
use NickMous\Binsta\Internals\Validation\ContextAwareValidationRule;

class SameRule implements ParameterizedValidationRule, ContextAwareValidationRule
{
    public const string KEY = 'same';

    private string $fieldToCompare = '';
    /**
     * @var array<string, mixed>
     */
    private array $context = [];

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
            $this->fieldToCompare = $parameters[0];
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function validate(mixed $value): bool
    {
        if (!isset($this->context[$this->fieldToCompare])) {
            return false;
        }

        return $value === $this->context[$this->fieldToCompare];
    }
}
