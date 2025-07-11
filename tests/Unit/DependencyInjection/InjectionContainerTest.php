<?php

require_once __DIR__ . '/TestClasses.php';

use NickMous\Binsta\Internals\DependencyInjection\InjectionContainer;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\DuplicatePrioritySetException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\NoClassFoundException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\NoPrioritySetException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\NoTypesException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\TooManyPriorityClassesException;
use NickMous\Binsta\Tests\Unit\DependencyInjection\AbstractClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\ConcreteImplementation;
use NickMous\Binsta\Tests\Unit\DependencyInjection\DependentClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\DuplicatePriorityInterface;
use NickMous\Binsta\Tests\Unit\DependencyInjection\HighPriorityOnly;
use NickMous\Binsta\Tests\Unit\DependencyInjection\NonInstantiableInterface;
use NickMous\Binsta\Tests\Unit\DependencyInjection\NonPriorityAttributeInterface;
use NickMous\Binsta\Tests\Unit\DependencyInjection\NoPriorityImplementation;
use NickMous\Binsta\Tests\Unit\DependencyInjection\PriorityTestInterface;
use NickMous\Binsta\Tests\Unit\DependencyInjection\SimpleClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\SingleImplementation;
use NickMous\Binsta\Tests\Unit\DependencyInjection\SingleImplementationInterface;
use NickMous\Binsta\Tests\Unit\DependencyInjection\TestInterface;
use NickMous\Binsta\Tests\Unit\DependencyInjection\TooManyPriorityInterface;
use NickMous\Binsta\Tests\Unit\DependencyInjection\UnionTypeParameterClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\UntypedParameterClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\WithPriorityAttributeClass;

covers(InjectionContainer::class);

describe('InjectionContainer', function (): void {
    beforeEach(function (): void {
        // Reset singleton instance between tests
        $reflection = new ReflectionClass(InjectionContainer::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    });

    describe('Singleton Pattern', function (): void {
        test('getInstance returns the same instance', function (): void {
            $container1 = InjectionContainer::getInstance();
            $container2 = InjectionContainer::getInstance();

            expect($container1)->toBe($container2);
        });

        test('getInstance returns InjectionContainer instance', function (): void {
            $container = InjectionContainer::getInstance();

            expect($container)->toBeInstanceOf(InjectionContainer::class);
        });
    });

    describe('Basic Dependency Resolution', function (): void {
        test('resolves simple class with no dependencies', function (): void {
            $container = InjectionContainer::getInstance();
            $instance = $container->get(SimpleClass::class);

            expect($instance)->toBeInstanceOf(SimpleClass::class);
            expect($instance->getValue())->toBe('simple');
        });

        test('returns same instance on subsequent calls (singleton behavior)', function (): void {
            $container = InjectionContainer::getInstance();
            $instance1 = $container->get(SimpleClass::class);
            $instance2 = $container->get(SimpleClass::class);

            expect($instance1)->toBe($instance2);
        });

        test('resolves class with dependencies', function (): void {
            $container = InjectionContainer::getInstance();
            $instance = $container->get(DependentClass::class);

            expect($instance)->toBeInstanceOf(DependentClass::class);
            expect($instance->getSimple())->toBeInstanceOf(SimpleClass::class);
        });
    });

    describe('Interface Resolution with Priorities', function (): void {
        test('throws NoPrioritySetException when some implementations lack priorities', function (): void {
            // TestInterface has multiple implementations, some without priorities
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(TestInterface::class))
                ->toThrow(NoPrioritySetException::class);
        });

        test('resolves interface with highest priority implementation', function (): void {
            // PriorityTestInterface has only implementations with priorities
            $container = InjectionContainer::getInstance();
            $instance = $container->get(PriorityTestInterface::class);

            expect($instance)->toBeInstanceOf(HighPriorityOnly::class);
            expect($instance->getPriority())->toBe('high-priority');
        });

        test('resolves interface with single implementation directly', function (): void {
            // Line 74 coverage: when there's exactly one implementation found
            $container = InjectionContainer::getInstance();
            $instance = $container->get(SingleImplementationInterface::class);

            expect($instance)->toBeInstanceOf(SingleImplementation::class);
            expect($instance->getSingle())->toBe('single');
        });

        test('skips non-instantiable implementations', function (): void {
            // Line 87 coverage: when a found class is not instantiable (abstract)
            $container = InjectionContainer::getInstance();
            $instance = $container->get(NonInstantiableInterface::class);

            expect($instance)->toBeInstanceOf(ConcreteImplementation::class);
            expect($instance->getType())->toBe('concrete');
        });

        test('throws DuplicatePrioritySetException for multiple priority attributes on same class', function (): void {
            // Line 98 coverage: when same class has multiple Priority attributes
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(DuplicatePriorityInterface::class))
                ->toThrow(DuplicatePrioritySetException::class);
        });

        test('throws TooManyPriorityClassesException for multiple classes with same priority', function (): void {
            // Line 109 coverage: when multiple classes have the same priority value
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(TooManyPriorityInterface::class))
                ->toThrow(TooManyPriorityClassesException::class);
        });

        test('skips non-Priority attributes when resolving interface', function (): void {
            // Line 94 coverage: when an attribute is not a Priority attribute
            $container = InjectionContainer::getInstance();
            $instance = $container->get(NonPriorityAttributeInterface::class);

            expect($instance)->toBeInstanceOf(WithPriorityAttributeClass::class);
            expect($instance->getValue())->toBe('with-priority-attribute');
        });
    });

    describe('Exception Cases', function (): void {
        test('throws ReflectionException for non-existent class', function (): void {
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get('NonExistentClass'))
                ->toThrow(ReflectionException::class);
        });

        test('throws NoClassFoundException for abstract class with no implementations', function (): void {
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(AbstractClass::class))
                ->toThrow(NoClassFoundException::class);
        });

        test('resolves concrete class without priority directly', function (): void {
            $container = InjectionContainer::getInstance();
            $instance = $container->get(NoPriorityImplementation::class);

            expect($instance)->toBeInstanceOf(NoPriorityImplementation::class);
            expect($instance->getName())->toBe('no-priority');
        });

        test('throws NoTypesException for constructor parameter without type', function (): void {
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(UntypedParameterClass::class))
                ->toThrow(NoTypesException::class);
        });

        test('throws NoTypesException for union type parameter', function (): void {
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(UnionTypeParameterClass::class))
                ->toThrow(NoTypesException::class);
        });
    });

    describe('Constructor Injection', function (): void {
        test('injects dependencies into constructor', function (): void {
            $container = InjectionContainer::getInstance();
            $dependent = $container->get(DependentClass::class);

            expect($dependent->getSimple())->toBeInstanceOf(SimpleClass::class);
            expect($dependent->getSimple()->getValue())->toBe('simple');
        });

        test('handles classes with no constructor', function (): void {
            $container = InjectionContainer::getInstance();
            $instance = $container->get(SimpleClass::class);

            expect($instance)->toBeInstanceOf(SimpleClass::class);
        });
    });
});
