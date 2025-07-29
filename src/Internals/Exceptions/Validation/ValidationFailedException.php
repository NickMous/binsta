<?php

namespace NickMous\Binsta\Internals\Exceptions\Validation;

class ValidationFailedException extends \Exception
{
    public function __construct(
        public readonly array $errors,
        public readonly bool $returnJson = false,
    ) {
        parent::__construct(
            'Validation failed',
            422,
            null,
        );
    }
}
