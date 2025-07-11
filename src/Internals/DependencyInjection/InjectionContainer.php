<?php

namespace NickMous\Binsta\Internals\DependencyInjection;

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

    private array $methods = [];

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

    public function execute(string $class, string $method, array $args = []): mixed
    {
        $correctClass = $this->getCorrectClass($class);
        $instance = $this->get($class);

        if (!method_exists($instance, $method)) {
            throw new \BadMethodCallException("Method {$method} does not exist in class {$class}");
        }

        $arguments = $this->getArguments($correctClass, $method);
        $argumentsInstantiated = array_map(function ($arg) {
            return $this->get($arg);
        }, $arguments);

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
            return is_object($arg) ? $arg : $this->get($arg);
        }, $arguments);

        return new $class(...$argumentsInstantiated);
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
        if (!isset($this->methods[$class])) {
            $this->methods[$class] = [];
        }

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
}
