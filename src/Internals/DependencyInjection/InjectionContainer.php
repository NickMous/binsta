<?php

namespace NickMous\Binsta\Internals\DependencyInjection;

use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\DuplicatePrioritySetException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\NoClassFoundException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\NoPrioritySetException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\NoTypesException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\TooManyPriorityClassesException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

class InjectionContainer
{
    private static ?InjectionContainer $instance = null;

    /**
     * @var array<string, string>
     */
    private array $services = [];

    /**
     * @var array<string, array<string, array<string>>>
     */
    private array $methods = [];

    /**
     * @var array<string, object>
     */
    private array $instances = [];

    private function __construct()
    {
    }

    public static function getInstance(): InjectionContainer
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @throws TooManyPriorityClassesException
     * @throws ReflectionException
     * @throws NoClassFoundException
     * @throws DuplicatePrioritySetException
     * @throws NoPrioritySetException
     * @throws NoTypesException
     */
    public function get(string $class): object
    {
        return $this->instantiateClass($this->getCorrectClass($class));
    }

    /**
     * @throws TooManyPriorityClassesException
     * @throws NoClassFoundException
     * @throws ReflectionException
     * @throws DuplicatePrioritySetException
     * @throws NoPrioritySetException
     * @throws NoTypesException
     */
    public function getCorrectClass(string $class): string
    {
        return $this->services[$class] ??= $this->resolve($class);
    }

    /**
     * @param array<string, mixed> $routeParameters
     */
    public function execute(string $class, string $method, array $routeParameters = []): mixed
    {
        $correctClass = $this->getCorrectClass($class);
        $instance = $this->get($class);

        if (!method_exists($instance, $method)) {
            throw new \BadMethodCallException("Method {$method} does not exist in class {$class}");
        }

        $argumentsInstantiated = $this->resolveMethodArguments($correctClass, $method, $routeParameters);

        return $instance->{$method}(...$argumentsInstantiated);
    }

    /**
     * @throws TooManyPriorityClassesException
     * @throws NoPrioritySetException
     * @throws ReflectionException
     * @throws NoClassFoundException
     * @throws DuplicatePrioritySetException
     * @throws NoTypesException
     */
    private function resolve(string $class): string
    {
        $reflection_class = new ReflectionClass($class);

        if ($reflection_class->isInstantiable()) {
            return $class;
        }

        $found_classes = [];

        foreach (get_declared_classes() as $declared_class) {
            if (is_subclass_of($declared_class, $class)) {
                $found_classes[] = $declared_class;
            }
        }

        if (count($found_classes) === 1) {
            return $found_classes[0];
        }

        if (count($found_classes) === 0) {
            throw new NoClassFoundException($class);
        }

        $what_priority = [];

        foreach ($found_classes as $found_class) {
            $reflection_class = new ReflectionClass($found_class);

            if (!$reflection_class->isInstantiable()) {
                continue;
            }

            $priority = null;

            foreach ($reflection_class->getAttributes() as $attribute) {
                if ($attribute->getName() !== 'NickMous\Binsta\Internals\Attributes\Priority') {
                    continue;
                }

                if ($priority !== null) {
                    throw new DuplicatePrioritySetException($found_class, (string)$priority);
                }

                $priority = $attribute->getArguments()[0];
            }

            if ($priority === null) {
                throw new NoPrioritySetException($class);
            }

            if (isset($what_priority[$priority])) {
                throw new TooManyPriorityClassesException($class, $priority);
            }

            $what_priority[$priority] = $found_class;
        }

        ksort($what_priority);

        return current($what_priority);
    }

    /**
     * @param string $class
     * @return object
     * @throws DuplicatePrioritySetException
     * @throws NoClassFoundException
     * @throws NoPrioritySetException
     * @throws NoTypesException
     * @throws ReflectionException
     * @throws TooManyPriorityClassesException
     */
    private function instantiateClass(string $class): object
    {
        // Check if class is marked as singleton
        $reflectionClass = new ReflectionClass($class);
        $isSingleton = false;

        foreach ($reflectionClass->getAttributes() as $attribute) {
            if ($attribute->getName() === 'NickMous\Binsta\Internals\Attributes\Singleton') {
                $isSingleton = true;
                break;
            }
        }

        // Return existing instance if singleton and already instantiated
        if ($isSingleton && isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        if (!isset($this->methods[$class])) {
            $this->methods[$class] = [];
        }

        if (isset($this->methods[$class]['__construct'])) {
            $arguments = $this->methods[$class]['__construct'];
        } else {
            $arguments = $this->getArguments($class);
            $this->methods[$class]['__construct'] = $arguments;
        }

        $argumentsInstantiated = array_map(function ($arg) {
            return $this->get($arg);
        }, $arguments);

        $instance = new $class(...$argumentsInstantiated);

        // Store instance if singleton
        if ($isSingleton) {
            $this->instances[$class] = $instance;
        }

        return $instance;
    }

    /**
     * @param string $class
     * @param string $methodName
     * @return array<string>
     * @throws DuplicatePrioritySetException
     * @throws NoClassFoundException
     * @throws NoPrioritySetException
     * @throws NoTypesException
     * @throws ReflectionException
     * @throws TooManyPriorityClassesException
     */
    private function getArguments(string $class, string $methodName = '__construct'): array
    {
        if (isset($this->methods[$class][$methodName])) {
            return $this->methods[$class][$methodName];
        }

        $reflection_class = new ReflectionClass($class);

        if (!$reflection_class->hasMethod($methodName)) {
            return [];
        }

        $constructor = $reflection_class->getMethod($methodName);

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType) {
                throw new NoTypesException($class, $parameter);
            }

            $arguments[] = $this->getCorrectClass($type->getName());
        }

        $this->methods[$class][$methodName] = $arguments;
        return $arguments;
    }

    /**
     * Resolve method arguments with both dependency injection and route parameter resolution
     * @param string $class
     * @param string $methodName
     * @param array<string, mixed> $routeParameters
     * @return array<mixed>
     * @throws ReflectionException
     */
    private function resolveMethodArguments(string $class, string $methodName, array $routeParameters = []): array
    {
        $reflectionClass = new ReflectionClass($class);

        if (!$reflectionClass->hasMethod($methodName)) {
            return [];
        }

        $method = $reflectionClass->getMethod($methodName);
        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $type = $parameter->getType();

            // Check if there's a route parameter matching this parameter name
            if (array_key_exists($parameterName, $routeParameters)) {
                // If the parameter has an Entity type hint, resolve the entity from the route parameter
                if ($type instanceof ReflectionNamedType && is_subclass_of($type->getName(), Entity::class)) {
                    $arguments[] = $this->resolveEntityFromParameter($type->getName(), (string)$routeParameters[$parameterName]);
                } else {
                    // Use the route parameter value directly
                    $arguments[] = $routeParameters[$parameterName];
                }
            } elseif ($type instanceof ReflectionNamedType) {
                // Standard dependency injection for classes
                $arguments[] = $this->get($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                // Use default value if available
                $arguments[] = $parameter->getDefaultValue();
            } else {
                throw new NoTypesException($class, $parameter);
            }
        }

        return $arguments;
    }

    /**
     * Resolve an entity from a route parameter value
     * @param string $entityClass
     * @param string $parameterValue
     * @return Entity
     * @throws ReflectionException
     */
    private function resolveEntityFromParameter(string $entityClass, string $parameterValue): Entity
    {
        $repositoryClass = $entityClass::getRepositoryClass();

        return $this->get($repositoryClass)->getEntityByParameter($parameterValue);
    }
}
