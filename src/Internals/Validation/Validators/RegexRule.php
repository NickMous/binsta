<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ParameterizedValidationRule;

class RegexRule implements ParameterizedValidationRule
{
    public const string KEY = 'regex';

    private string $pattern = '';

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
            $this->pattern = $parameters[0];
        }
    }

    public function validate(mixed $value): bool
    {
        if (!is_string($value) || empty($this->pattern)) {
            return false;
        }

        $result = @preg_match($this->pattern, $value);

        // preg_match returns false on error, 0 for no match, 1 for match
        return $result === 1;
    }
}
