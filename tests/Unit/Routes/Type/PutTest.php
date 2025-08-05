<?php

use NickMous\Binsta\Internals\Routes\Type\Put;
use NickMous\Binsta\Internals\Response\JsonResponse;

covers(Put::class);

describe('Put', function (): void {
    test('creates PUT route with closure', function (): void {
        $closure = fn() => new JsonResponse(['message' => 'PUT route executed']);
        $route = new Put('/api/users/{id}', $closure);

        expect($route->path)->toBe('/api/users/{id}');
        expect($route->method)->toBe('PUT');

        // Use reflection to access protected properties
        $reflection = new ReflectionClass($route);
        $closureProperty = $reflection->getProperty('closure');
        $closureProperty->setAccessible(true);
        expect($closureProperty->getValue($route))->toBe($closure);
    });

    test('creates PUT route with class and method', function (): void {
        $route = new Put('/api/users/{id}', null, 'UserController', 'update');

        expect($route->path)->toBe('/api/users/{id}');
        expect($route->method)->toBe('PUT');

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
        expect($methodNameProperty->getValue($route))->toBe('update');
    });

    test('creates PUT route with all parameters', function (): void {
        $closure = fn() => new JsonResponse(['updated' => true]);
        $route = new Put('/api/users/{id}', $closure, 'UserController', 'update');

        expect($route->path)->toBe('/api/users/{id}');
        expect($route->method)->toBe('PUT');

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
        expect($methodNameProperty->getValue($route))->toBe('update');
    });

    test('creates PUT route with no parameters except path', function (): void {
        $route = new Put('/api/users/{id}');

        expect($route->path)->toBe('/api/users/{id}');
        expect($route->method)->toBe('PUT');

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
