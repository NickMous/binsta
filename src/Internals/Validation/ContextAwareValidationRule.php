<?php

namespace NickMous\Binsta\Internals\Validation;

interface ContextAwareValidationRule extends ValidationRule
{
    /**
     * @param array<string, mixed> $context
     */
    public function setContext(array $context): void;
}
