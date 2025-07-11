<?php

namespace NickMous\Binsta\Tests\Unit\DependencyInjection;

use NickMous\Binsta\Internals\Attributes\Priority;

// Simple class with no dependencies
class SimpleClass
{
    public function getValue(): string
    {
        return 'simple';
    }
}

// Class with dependencies
class DependentClass
{
    public function __construct(private SimpleClass $simple)
    {
    }

    public function getSimple(): SimpleClass
    {
        return $this->simple;
    }
}

// Interface for priority testing
interface TestInterface
{
    public function getName(): string;
}

// Implementation without priority (should cause exception)
class NoPriorityImplementation implements TestInterface
{
    public function getName(): string
    {
        return 'no-priority';
    }
}

// Implementation with priority 1
#[Priority(1)]
class HighPriorityImplementation implements TestInterface
{
    public function getName(): string
    {
        return 'high-priority';
    }
}

// Implementation with priority 2
#[Priority(2)]
class LowPriorityImplementation implements TestInterface
{
    public function getName(): string
    {
        return 'low-priority';
    }
}

// Implementation with duplicate priority
#[Priority(1)]
class DuplicatePriorityImplementation implements TestInterface
{
    public function getName(): string
    {
        return 'duplicate-priority';
    }
}

// Implementation with multiple priority attributes
#[Priority(1)]
#[Priority(2)]
class MultiplePriorityImplementation implements TestInterface
{
    public function getName(): string
    {
        return 'multiple-priority';
    }
}

// Class with untyped parameter
class UntypedParameterClass
{
    public function __construct($untyped)
    {
    }
}

// Class with union type parameter
class UnionTypeParameterClass
{
    public function __construct(string|int $param)
    {
    }
}

// Abstract class (not instantiable)
abstract class AbstractClass
{
    abstract public function doSomething(): string;
}

// Separate interface for priority testing (only has implementations with priorities)
interface PriorityTestInterface
{
    public function getPriority(): string;
}

// Implementation with priority 1
#[Priority(1)]
class HighPriorityOnly implements PriorityTestInterface
{
    public function getPriority(): string
    {
        return 'high-priority';
    }
}

// Implementation with priority 2
#[Priority(2)]
class LowPriorityOnly implements PriorityTestInterface
{
    public function getPriority(): string
    {
        return 'low-priority';
    }
}

// Interface with single implementation (for line 74 coverage)
interface SingleImplementationInterface
{
    public function getSingle(): string;
}

// Single implementation
#[Priority(1)]
class SingleImplementation implements SingleImplementationInterface
{
    public function getSingle(): string
    {
        return 'single';
    }
}

// Interface with non-instantiable implementation (for line 87 coverage)
interface NonInstantiableInterface
{
    public function getType(): string;
}

// Abstract implementation (not instantiable)
abstract class AbstractImplementation implements NonInstantiableInterface
{
    abstract public function getType(): string;
}

// Concrete implementation
#[Priority(1)]
class ConcreteImplementation implements NonInstantiableInterface
{
    public function getType(): string
    {
        return 'concrete';
    }
}

// Interface for testing duplicate priority exception (line 98)
interface DuplicatePriorityInterface
{
    public function getValue(): string;
}

// Class with duplicate priority attributes (multiple Priority attributes on same class)
#[Priority(1)]
#[Priority(2)]
class DuplicatePriorityAttributesClass implements DuplicatePriorityInterface
{
    public function getValue(): string
    {
        return 'duplicate-attributes';
    }
}

// Single implementation for DuplicatePriorityInterface to make it work
#[Priority(1)]
class SingleDuplicatePriorityClass implements DuplicatePriorityInterface
{
    public function getValue(): string
    {
        return 'single-duplicate';
    }
}

// Interface for testing non-Priority attribute skipping (line 94)
interface NonPriorityAttributeInterface
{
    public function getValue(): string;
}

// Interface for testing too many priority classes exception (line 109)
interface TooManyPriorityInterface
{
    public function getName(): string;
}

// First class with priority 1
#[Priority(1)]
class FirstPriorityClass implements TooManyPriorityInterface
{
    public function getName(): string
    {
        return 'first';
    }
}

// Second class with same priority 1 (should cause TooManyPriorityClassesException)
#[Priority(1)]
class SecondPriorityClass implements TooManyPriorityInterface
{
    public function getName(): string
    {
        return 'second';
    }
}

// Class with non-Priority attribute (for line 94 coverage)
#[SuppressWarnings('unused')]
#[Priority(2)]
class NonPriorityAttributeClass implements NonPriorityAttributeInterface
{
    public function getValue(): string
    {
        return 'non-priority-attribute';
    }
}

// Class with Priority attribute for the same interface
#[Priority(1)]
class WithPriorityAttributeClass implements NonPriorityAttributeInterface
{
    public function getValue(): string
    {
        return 'with-priority-attribute';
    }
}

// Custom attribute for testing
#[\Attribute]
class SuppressWarnings
{
    public function __construct(public string $value)
    {
    }
}
