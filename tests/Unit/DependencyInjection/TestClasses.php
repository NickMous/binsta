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
