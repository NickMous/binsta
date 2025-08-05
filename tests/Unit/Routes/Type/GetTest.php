<?php

use NickMous\Binsta\Internals\Routes\Type\Get;
use NickMous\Binsta\Internals\Response\JsonResponse;

covers(Get::class);

describe('Get', function (): void {
    test('creates GET route with closure', function (): void {
        $closure = fn() => new JsonResponse(['message' => 'GET route executed']);
        $route = new Get('/api/users', $closure);

        expect($route->path)->toBe('/api/users');
        expect($route->method)->toBe('GET');

        // Use reflection to access protected properties
        $reflection = new ReflectionClass($route);
        $closureProperty = $reflection->getProperty('closure');
        $closureProperty->setAccessible(true);
        expect($closureProperty->getValue($route))->toBe($closure);
    });

    test('creates GET route with class and method', function (): void {
        $route = new Get('/api/users', null, 'UserController', 'index');

        expect($route->path)->toBe('/api/users');
        expect($route->method)->toBe('GET');

        // Use reflection to access protected properties
        $reflection = new ReflectionClass($route);
        $closureProperty = $reflection->getProperty('closure');
        $closureProperty->setAccessible(true);
        expect($closureProperty->getValue($route))->toBeNull();

        $classNameProperty = $reflection->getProperty('className');
        $classNameProperty->setAccessible(true);
        expect($classNameProperty->getValue($route))->toBe('UserController');

        $methodNameProperty = $reflection->getProperty('methodName');
        $methodNameProperty->setAccessible(true);
        expect($methodNameProperty->getValue($route))->toBe('index');
    });

    test('creates GET route with all parameters', function (): void {
        $closure = fn() => new JsonResponse(['users' => []]);
        $route = new Get('/api/users', $closure, 'UserController', 'index');

        expect($route->path)->toBe('/api/users');
        expect($route->method)->toBe('GET');

        // Use reflection to access protected properties
        $reflection = new ReflectionClass($route);
        $closureProperty = $reflection->getProperty('closure');
        $closureProperty->setAccessible(true);
        expect($closureProperty->getValue($route))->toBe($closure);

        $classNameProperty = $reflection->getProperty('className');
        $classNameProperty->setAccessible(true);
        expect($classNameProperty->getValue($route))->toBe('UserController');

        $methodNameProperty = $reflection->getProperty('methodName');
        $methodNameProperty->setAccessible(true);
        expect($methodNameProperty->getValue($route))->toBe('index');
    });

    test('creates GET route with no parameters except path', function (): void {
        $route = new Get('/api/users');

        expect($route->path)->toBe('/api/users');
        expect($route->method)->toBe('GET');

        // Use reflection to access protected properties
        $reflection = new ReflectionClass($route);
        $closureProperty = $reflection->getProperty('closure');
        $closureProperty->setAccessible(true);
        expect($closureProperty->getValue($route))->toBeNull();

        $classNameProperty = $reflection->getProperty('className');
        $classNameProperty->setAccessible(true);
        expect($classNameProperty->getValue($route))->toBeNull();

        $methodNameProperty = $reflection->getProperty('methodName');
        $methodNameProperty->setAccessible(true);
        expect($methodNameProperty->getValue($route))->toBeNull();
    });
});
