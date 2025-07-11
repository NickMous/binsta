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
     * @var array<string, object>
     */
    private array $services = [];

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
        return $this->services[$class] ??= $this->resolve($class);
    }

    /**
     * @throws TooManyPriorityClassesException
     * @throws NoPrioritySetException
     * @throws ReflectionException
     * @throws NoClassFoundException
     * @throws DuplicatePrioritySetException
     * @throws NoTypesException
     */
    private function resolve(string $class): object
    {
        $reflection_class = new ReflectionClass($class);

        if ($reflection_class->isInstantiable()) {
            return $this->instantiateClass($class);
        }

        $found_classes = [];

        foreach (get_declared_classes() as $declared_class) {
            if (is_subclass_of($declared_class, $class)) {
                $found_classes[] = $declared_class;
            }
        }

        if (count($found_classes) === 1) {
            return $this->instantiateClass($found_classes[0]);
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
                if ($attribute->getName() !== 'Priority') {
                    continue;
                }

                if ($priority !== null) {
                    throw new DuplicatePrioritySetException($class, $priority);
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
        $class_with_highest_priority = current($what_priority);

        return $this->instantiateClass($class_with_highest_priority);
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
        $arguments = $this->getArguments($class);
        return new $class(...$arguments);
    }

    /**
     * @param string $class
     * @return array<object>
     * @throws DuplicatePrioritySetException
     * @throws NoClassFoundException
     * @throws NoPrioritySetException
     * @throws NoTypesException
     * @throws ReflectionException
     * @throws TooManyPriorityClassesException
     */
    private function getArguments(string $class): array
    {
        $reflection_class = new ReflectionClass($class);
        $constructor = $reflection_class->getConstructor();

        if (!$constructor) {
            return [];
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType) {
                throw new NoTypesException($class, $parameter);
            }

            $arguments[] = $this->get($type->getName());
        }

        return $arguments;
    }
}
