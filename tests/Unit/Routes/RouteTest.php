<?php

use NickMous\Binsta\Internals\Routes\Route;
use NickMous\Binsta\Internals\Routes\Type\Get;
use NickMous\Binsta\Internals\Routes\Type\Post;
use NickMous\Binsta\Internals\Routes\Type\Group;
use NickMous\Binsta\Internals\Response\JsonResponse;

covers(Route::class);

describe('Route', function (): void {
    test('creates GET route with static method', function (): void {
        $closure = function () {
            return new JsonResponse(['method' => 'GET']);
        };

        $route = Route::get('/test', $closure, 'TestController', 'index');

        expect($route)->toBeInstanceOf(Get::class);
        expect($route->path)->toBe('/test');
        expect($route->method)->toBe('GET');

        // Use reflection to access protected properties
        $reflection = new ReflectionClass($route);
        $closureProperty = $reflection->getProperty('closure');
        $closureProperty->setAccessible(true);
        expect($closureProperty->getValue($route))->toBe($closure);

        $classNameProperty = $reflection->getProperty('className');
        $classNameProperty->setAccessible(true);
        expect($classNameProperty->getValue($route))->toBe('TestController');

        $methodNameProperty = $reflection->getProperty('methodName');
        $methodNameProperty->setAccessible(true);
        expect($methodNameProperty->getValue($route))->toBe('index');
    });

    test('creates POST route with static method', function (): void {
        $closure = function () {
            return new JsonResponse(['method' => 'POST']);
        };

        $route = Route::post('/api/create', $closure, 'ApiController', 'create');

        expect($route)->toBeInstanceOf(Post::class);
        expect($route->path)->toBe('/api/create');
        expect($route->method)->toBe('POST');

        // Use reflection to access protected properties
        $reflection = new ReflectionClass($route);
        $closureProperty = $reflection->getProperty('closure');
        $closureProperty->setAccessible(true);
        expect($closureProperty->getValue($route))->toBe($closure);

        $classNameProperty = $reflection->getProperty('className');
        $classNameProperty->setAccessible(true);
        expect($classNameProperty->getValue($route))->toBe('ApiController');

        $methodNameProperty = $reflection->getProperty('methodName');
        $methodNameProperty->setAccessible(true);
        expect($methodNameProperty->getValue($route))->toBe('create');
    });

    test('creates POST route with minimal parameters', function (): void {
        $route = Route::post('/api/minimal');

        expect($route)->toBeInstanceOf(Post::class);
        expect($route->path)->toBe('/api/minimal');
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

    test('creates Group route with static method', function (): void {
        $getRoute = Route::get('/users', null, 'UserController', 'index');
        $postRoute = Route::post('/users', null, 'UserController', 'store');

        $group = Route::group('/api', [$getRoute, $postRoute]);

        expect($group)->toBeInstanceOf(Group::class);
        expect($group->path)->toBe('/api');
        expect($group->routes)->toBe([$getRoute, $postRoute]);
    });

    test('creates nested Group routes', function (): void {
        $userRoutes = Route::group('/users', [
            Route::get('/', null, 'UserController', 'index'),
            Route::post('/', null, 'UserController', 'store'),
        ]);

        $apiGroup = Route::group('/api', [$userRoutes]);

        expect($apiGroup)->toBeInstanceOf(Group::class);
        expect($apiGroup->path)->toBe('/api');
        expect($apiGroup->routes)->toHaveCount(1);
        expect($apiGroup->routes[0])->toBe($userRoutes);
    });
});
