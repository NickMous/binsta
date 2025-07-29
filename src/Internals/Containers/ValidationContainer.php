<?php

namespace NickMous\Binsta\Internals\Containers;

use NickMous\Binsta\Internals\Exceptions\Validation\DuplicateValidationRuleException;
use NickMous\Binsta\Internals\Validation\ValidationRule;
use NickMous\Binsta\Internals\Validation\Validators\RequiredRule;

class ValidationContainer
{
    private static ?ValidationContainer $instance = null;

    /**
     * @var array<string, array<string, string|ValidationRule>>
     */
    private array $validationRules = [];

    public static function getInstance(): ValidationContainer
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->loadValidationRules();
    }

    private function loadValidationRules(): void
    {
        $validatorsPaths = [
            __DIR__ . '/../Validation/Validators',
        ];

        foreach ($validatorsPaths as $validatorsPath) {
            if (!is_dir($validatorsPath)) {
                continue;
            }

            $files = glob($validatorsPath . '/*.php');

            foreach ($files as $file) {
                require_once $file;
            }
        }

        foreach (get_declared_classes() as $declared_class) {
            if (!in_array(ValidationRule::class, class_implements($declared_class) ?: [], true)) {
                continue;
            }

            $instance = new $declared_class();

            if (isset($this->validationRules[$instance->getKey()])) {
                throw new DuplicateValidationRuleException($instance->getKey());
            }

            $this->validationRules[$instance->getKey()] = [
                'class' => $declared_class,
                'instance' => $instance,
            ];
        }
    }

    public function getValidator(string $ruleName): ValidationRule
    {
        if (!isset($this->validationRules[$ruleName])) {
            throw new \InvalidArgumentException("Validation rule '{$ruleName}' not found.");
        }

        return $this->validationRules[$ruleName]['instance'];
    }
}
