<?php

require_once __DIR__ . '/TestClasses.php';

use NickMous\Binsta\Internals\DependencyInjection\InjectionContainer;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\NoClassFoundException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\NoPrioritySetException;
use NickMous\Binsta\Internals\Exceptions\DependencyInjection\NoTypesException;
use NickMous\Binsta\Tests\Unit\DependencyInjection\AbstractClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\DependentClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\HighPriorityOnly;
use NickMous\Binsta\Tests\Unit\DependencyInjection\NoPriorityImplementation;
use NickMous\Binsta\Tests\Unit\DependencyInjection\PriorityTestInterface;
use NickMous\Binsta\Tests\Unit\DependencyInjection\SimpleClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\TestInterface;
use NickMous\Binsta\Tests\Unit\DependencyInjection\UnionTypeParameterClass;
use NickMous\Binsta\Tests\Unit\DependencyInjection\UntypedParameterClass;

describe('InjectionContainer', function () {
    beforeEach(function () {
        // Reset singleton instance between tests
        $reflection = new ReflectionClass(InjectionContainer::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    });

    describe('Singleton Pattern', function () {
        test('getInstance returns the same instance', function () {
            $container1 = InjectionContainer::getInstance();
            $container2 = InjectionContainer::getInstance();

            expect($container1)->toBe($container2);
        });

        test('getInstance returns InjectionContainer instance', function () {
            $container = InjectionContainer::getInstance();

            expect($container)->toBeInstanceOf(InjectionContainer::class);
        });
    });

    describe('Basic Dependency Resolution', function () {
        test('resolves simple class with no dependencies', function () {
            $container = InjectionContainer::getInstance();
            $instance = $container->get(SimpleClass::class);

            expect($instance)->toBeInstanceOf(SimpleClass::class);
            expect($instance->getValue())->toBe('simple');
        });

        test('returns same instance on subsequent calls (singleton behavior)', function () {
            $container = InjectionContainer::getInstance();
            $instance1 = $container->get(SimpleClass::class);
            $instance2 = $container->get(SimpleClass::class);

            expect($instance1)->toBe($instance2);
        });

        test('resolves class with dependencies', function () {
            $container = InjectionContainer::getInstance();
            $instance = $container->get(DependentClass::class);

            expect($instance)->toBeInstanceOf(DependentClass::class);
            expect($instance->getSimple())->toBeInstanceOf(SimpleClass::class);
        });
    });

    describe('Interface Resolution with Priorities', function () {
        test('throws NoPrioritySetException when some implementations lack priorities', function () {
            // TestInterface has multiple implementations, some without priorities
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(TestInterface::class))
                ->toThrow(NoPrioritySetException::class);
        });

        test('resolves interface with highest priority implementation', function () {
            // PriorityTestInterface has only implementations with priorities
            $container = InjectionContainer::getInstance();
            $instance = $container->get(PriorityTestInterface::class);

            expect($instance)->toBeInstanceOf(HighPriorityOnly::class);
            expect($instance->getPriority())->toBe('high-priority');
        });
    });

    describe('Exception Cases', function () {
        test('throws ReflectionException for non-existent class', function () {
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get('NonExistentClass'))
                ->toThrow(ReflectionException::class);
        });

        test('throws NoClassFoundException for abstract class with no implementations', function () {
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(AbstractClass::class))
                ->toThrow(NoClassFoundException::class);
        });

        test('resolves concrete class without priority directly', function () {
            $container = InjectionContainer::getInstance();
            $instance = $container->get(NoPriorityImplementation::class);

            expect($instance)->toBeInstanceOf(NoPriorityImplementation::class);
            expect($instance->getName())->toBe('no-priority');
        });

        test('throws NoTypesException for constructor parameter without type', function () {
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(UntypedParameterClass::class))
                ->toThrow(NoTypesException::class);
        });

        test('throws NoTypesException for union type parameter', function () {
            $container = InjectionContainer::getInstance();

            expect(fn() => $container->get(UnionTypeParameterClass::class))
                ->toThrow(NoTypesException::class);
        });
    });

    describe('Constructor Injection', function () {
        test('injects dependencies into constructor', function () {
            $container = InjectionContainer::getInstance();
            $dependent = $container->get(DependentClass::class);

            expect($dependent->getSimple())->toBeInstanceOf(SimpleClass::class);
            expect($dependent->getSimple()->getValue())->toBe('simple');
        });

        test('handles classes with no constructor', function () {
            $container = InjectionContainer::getInstance();
            $instance = $container->get(SimpleClass::class);

            expect($instance)->toBeInstanceOf(SimpleClass::class);
        });
    });
});
