<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ParameterizedValidationRule;

class InRule implements ParameterizedValidationRule
{
    public const string KEY = 'in';

    /**
     * @var array<string>
     */
    private array $allowedValues = [];

    public function getKey(): string
    {
        return self::KEY;
    }

    /**
     * @param array<int, string> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->allowedValues = $parameters;
    }

    public function validate(mixed $value): bool
    {
        // Convert value to string for comparison
        $stringValue = (string) $value;

        return in_array($stringValue, $this->allowedValues, true);
    }
}
