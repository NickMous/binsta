<?php

namespace NickMous\Binsta\Internals\Validation;

interface ValidationRule
{
    public function getKey(): string;

    public function validate(mixed $value): bool;
}
