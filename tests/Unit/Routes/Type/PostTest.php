<?php

use NickMous\Binsta\Internals\Routes\Type\Post;
use NickMous\Binsta\Internals\Response\JsonResponse;

covers(Post::class);

describe('Post', function (): void {
    test('creates POST route with closure', function (): void {
        $closure = function () {
            return new JsonResponse(['method' => 'POST']);
        };

        $route = new Post('/api/test', $closure);

        expect($route->path)->toBe('/api/test');
        expect($route->method)->toBe('POST');
        
        // Use reflection to access protected properties
        $reflection = new ReflectionClass($route);
        $closureProperty = $reflection->getProperty('closure');
        $closureProperty->setAccessible(true);
        expect($closureProperty->getValue($route))->toBe($closure);
        
        $classNameProperty = $reflection->getProperty('className');
        $classNameProperty->setAccessible(true);
        expect($classNameProperty->getValue($route))->toBeNull();
        
        $methodNameProperty = $reflection->getProperty('methodName');
        $methodNameProperty->setAccessible(true);
        expect($methodNameProperty->getValue($route))->toBeNull();
    });

    test('creates POST route with class and method', function (): void {
        $route = new Post('/api/users', null, 'UserController', 'store');

        expect($route->path)->toBe('/api/users');
        expect($route->method)->toBe('POST');
        
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
        expect($methodNameProperty->getValue($route))->toBe('store');
    });

    test('creates POST route with all parameters', function (): void {
        $closure = function () {
            return new JsonResponse(['created' => true]);
        };

        $route = new Post('/api/posts', $closure, 'PostController', 'create');

        expect($route->path)->toBe('/api/posts');
        expect($route->method)->toBe('POST');
        
        // Use reflection to access protected properties
        $reflection = new ReflectionClass($route);
        $closureProperty = $reflection->getProperty('closure');
        $closureProperty->setAccessible(true);
        expect($closureProperty->getValue($route))->toBe($closure);
        
        $classNameProperty = $reflection->getProperty('className');
        $classNameProperty->setAccessible(true);
        expect($classNameProperty->getValue($route))->toBe('PostController');
        
        $methodNameProperty = $reflection->getProperty('methodName');
        $methodNameProperty->setAccessible(true);
        expect($methodNameProperty->getValue($route))->toBe('create');
    });

    test('creates POST route with no parameters except path', function (): void {
        $route = new Post('/api/empty');

        expect($route->path)->toBe('/api/empty');
        expect($route->method)->toBe('POST');
        
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