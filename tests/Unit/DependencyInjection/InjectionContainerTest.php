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
use NickMous\Binsta\Tests\Unit\DependencyInjection\MethodWithParametersClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\FreshClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\SingletonClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\NonSingletonClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\SingletonWithDependencies;

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

        test('returns new instance on subsequent calls (non-singleton behavior)', function (): void {
            $container = InjectionContainer::getInstance();
            $instance1 = $container->get(SimpleClass::class);
            $instance2 = $container->get(SimpleClass::class);

            // SimpleClass is not marked as singleton, so should get new instances
            expect($instance1)->not->toBe($instance2);
            expect($instance1)->toBeInstanceOf(SimpleClass::class);
            expect($instance2)->toBeInstanceOf(SimpleClass::class);
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

    describe('Method Execution', function (): void {
        test('executes method with dependency injection', function (): void {
            $container = InjectionContainer::getInstance();
            $result = $container->execute(DependentClass::class, 'getSimple');

            expect($result)->toBeInstanceOf(SimpleClass::class);
            expect($result->getValue())->toBe('simple');
        });

        test('executes method with method parameters', function (): void {
            $container = InjectionContainer::getInstance();
            $result = $container->execute(MethodWithParametersClass::class, 'processSimple');

            expect($result)->toBe('processed: simple');
        });

        test('throws BadMethodCallException for non-existent method', function (): void {
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->execute(SimpleClass::class, 'nonExistentMethod'))
                ->toThrow(BadMethodCallException::class, 'Method nonExistentMethod does not exist in class');
        });

        test('caches method arguments for performance', function (): void {
            $container = InjectionContainer::getInstance();

            // First call - should cache arguments
            $result1 = $container->execute(MethodWithParametersClass::class, 'processSimple');

            // Second call - should use cached arguments
            $result2 = $container->execute(MethodWithParametersClass::class, 'processSimple');

            expect($result1)->toBe('processed: simple');
            expect($result2)->toBe('processed: simple');
        });

        test('handles methods with no parameters', function (): void {
            $container = InjectionContainer::getInstance();
            $result = $container->execute(SimpleClass::class, 'getValue');

            expect($result)->toBe('simple');
        });

        test('initializes method cache for new classes', function (): void {
            $container = InjectionContainer::getInstance();
            // Use a fresh class that hasn't been used before to hit the cache initialization
            $result = $container->execute(FreshClass::class, 'freshMethod');

            expect($result)->toBe('fresh');
        });

        test('initializes method cache for new methods on existing classes', function (): void {
            $container = InjectionContainer::getInstance();
            // First, instantiate the class (this will initialize the methods array)
            $container->get(FreshClass::class);

            // Now call a different method - this should hit the cache initialization for this specific method
            $result = $container->execute(FreshClass::class, 'anotherMethod');

            expect($result)->toBe('another');
        });
    });

    describe('Singleton Support', function (): void {
        beforeEach(function (): void {
            // Reset instantiation counters before each test
            SingletonClass::resetInstantiationCount();
            NonSingletonClass::resetInstantiationCount();
            SingletonWithDependencies::resetInstantiationCount();
        });

        test('returns same instance for singleton classes', function (): void {
            $container = InjectionContainer::getInstance();

            $instance1 = $container->get(SingletonClass::class);
            $instance2 = $container->get(SingletonClass::class);

            expect($instance1)->toBe($instance2);
            expect($instance1->getInstanceId())->toBe(1);
            expect($instance2->getInstanceId())->toBe(1);
            expect(SingletonClass::getInstantiationCount())->toBe(1);
        });

        test('returns different instances for non-singleton classes', function (): void {
            $container = InjectionContainer::getInstance();

            $instance1 = $container->get(NonSingletonClass::class);
            $instance2 = $container->get(NonSingletonClass::class);

            expect($instance1)->not->toBe($instance2);
            expect($instance1->getInstanceId())->toBe(1);
            expect($instance2->getInstanceId())->toBe(2);
            expect(NonSingletonClass::getInstantiationCount())->toBe(2);
        });

        test('works with singleton classes that have dependencies', function (): void {
            $container = InjectionContainer::getInstance();

            $instance1 = $container->get(SingletonWithDependencies::class);
            $instance2 = $container->get(SingletonWithDependencies::class);

            expect($instance1)->toBe($instance2);
            expect($instance1->getInstanceId())->toBe(1);
            expect($instance2->getInstanceId())->toBe(1);
            expect(SingletonWithDependencies::getInstantiationCount())->toBe(1);
            expect($instance1->getSimple())->toBeInstanceOf(SimpleClass::class);
            expect($instance2->getSimple())->toBeInstanceOf(SimpleClass::class);
        });

        test('singleton behavior works with execute method', function (): void {
            $container = InjectionContainer::getInstance();

            // Execute method on singleton class multiple times
            $result1 = $container->execute(SingletonClass::class, 'getInstanceId');
            $result2 = $container->execute(SingletonClass::class, 'getInstanceId');

            expect($result1)->toBe(1);
            expect($result2)->toBe(1);
            expect(SingletonClass::getInstantiationCount())->toBe(1);
        });

        test('singleton instances are stored separately per class', function (): void {
            $container = InjectionContainer::getInstance();

            $singleton1 = $container->get(SingletonClass::class);
            $singleton2 = $container->get(SingletonClass::class);
            $singletonWithDeps1 = $container->get(SingletonWithDependencies::class);
            $singletonWithDeps2 = $container->get(SingletonWithDependencies::class);

            // Same class instances should be identical
            expect($singleton1)->toBe($singleton2);
            expect($singletonWithDeps1)->toBe($singletonWithDeps2);

            // Different class instances should be different objects
            expect($singleton1)->not->toBe($singletonWithDeps1);

            // Each singleton class should be instantiated only once
            expect(SingletonClass::getInstantiationCount())->toBe(1);
            expect(SingletonWithDependencies::getInstantiationCount())->toBe(1);
        });
    });
});
