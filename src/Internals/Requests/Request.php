<?php

namespace NickMous\Binsta\Internals\Requests;

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

        // Store POST parameters (POST takes precedence over GET)
        foreach ($_POST as $key => $value) {
            $this->parameters[$key] = $value;
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
}
