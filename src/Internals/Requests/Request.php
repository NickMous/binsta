<?php

namespace NickMous\Binsta\Internals\Requests;

use InvalidArgumentException;
use NickMous\Binsta\Internals\Containers\ValidationContainer;
use NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException;
use NickMous\Binsta\Internals\Validation\HasValidation;

class Request
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters = [];

    public function __construct()
    {
        // Store GET parameters
        foreach ($_GET as $key => $value) {
            $this->parameters[$key] = $value;
        }

        // Handle JSON data for POST, PUT, PATCH requests
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'])) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (str_contains($contentType, 'application/json')) {
                $jsonData = json_decode(file_get_contents('php://input'), true);
                if (is_array($jsonData)) {
                    foreach ($jsonData as $key => $value) {
                        $this->parameters[$key] = $value;
                    }
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Store regular POST parameters (POST takes precedence over GET)
                foreach ($_POST as $key => $value) {
                    $this->parameters[$key] = $value;
                }
            }
        }

        // Apply transformations if the request implements HasTransformation
        if ($this instanceof HasTransformation) {
            $this->parameters = $this->transform($this->parameters);
        }
    }

    /**
     * Magic method to get parameter values
     */
    public function __get(string $name): mixed
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * Magic method to set parameter values
     */
    public function __set(string $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Magic method to check if parameter exists
     */
    public function __isset(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Get all parameters
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * Get a parameter with a default value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * Check if parameter exists
     */
    public function has(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function validate(bool $returnJson = false): void
    {
        if (!$this instanceof HasValidation) {
            return;
        }

        $fieldsToValidate = $this->rules();
        $messages = $this->messages();
        $validationContainer = ValidationContainer::getInstance();
        $errors = [];

        foreach ($fieldsToValidate as $field => $rules) {
            $value = $this->get($field);

            // For file uploads, check $_FILES if value is null
            if ($value === null && isset($_FILES[$field])) {
                $value = $_FILES[$field];
            }

            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }

            // Check if 'sometimes' rule is present
            $hasSometimes = in_array('sometimes', $rules);
            if ($hasSometimes) {
                // If field is not present and has 'sometimes', skip validation entirely
                if (!$this->has($field) && !isset($_FILES[$field])) {
                    continue;
                }
                // Remove 'sometimes' from rules array as it doesn't need actual validation
                $rules = array_filter($rules, fn($rule) => $rule !== 'sometimes');
            }

            foreach ($rules as $rule) {
                if (isset($errors[$field])) {
                    continue;
                }

                // Parse rule and parameters (e.g., "min:8" -> rule="min", params=["8"])
                $ruleParts = explode(':', $rule, 2);
                $ruleName = $ruleParts[0];
                $parameters = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

                try {
                    $validator = $validationContainer->createValidator($ruleName, $parameters, $this->all());

                    if (!$validator->validate($value)) {
                        $messageKey = $field . '.' . $ruleName;
                        $errors[$field] = $messages[$messageKey] ?? $messageKey;
                    }
                } catch (InvalidArgumentException) {
                    // Fallback to simple validator for backward compatibility
                    try {
                        $validator = $validationContainer->getValidator($ruleName);
                        if (!$validator->validate($value)) {
                            $messageKey = $field . '.' . $ruleName;
                            $errors[$field] = $messages[$messageKey] ?? $messageKey;
                        }
                    } catch (InvalidArgumentException) {
                        $errors[$field] = "Unknown validation rule: {$ruleName}";
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationFailedException($errors, $returnJson);
        }
    }
}
