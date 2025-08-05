<?php

use NickMous\Binsta\Controllers\AuthController;
use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Requests\Auth\LoginRequest;
use NickMous\Binsta\Requests\Auth\RegisterRequest;

covers(AuthController::class);

describe('AuthController', function (): void {
    beforeEach(function (): void {
        // Clear session for each test
        $_SESSION = [];
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    });

    afterEach(function (): void {
        $_SESSION = [];
    });

    describe('saveUserSession', function (): void {
        test('saves user ID to session', function (): void {
            $mockUser = $this->createMock(User::class);
            $mockUser->method('getId')->willReturn(123);

            $controller = new AuthController();

            // Use reflection to access the protected method
            $reflection = new ReflectionClass($controller);
            $method = $reflection->getMethod('saveUserSession');
            $method->setAccessible(true);

            // Call the method
            $method->invoke($controller, $mockUser);

            // Assert that the session was set correctly
            expect($_SESSION['user'])->toBe(123);
        });

        test('overwrites existing session user', function (): void {
            $_SESSION['user'] = 456; // Set initial user

            $mockUser = $this->createMock(User::class);
            $mockUser->method('getId')->willReturn(789);

            $controller = new AuthController();

            // Use reflection to access the protected method
            $reflection = new ReflectionClass($controller);
            $method = $reflection->getMethod('saveUserSession');
            $method->setAccessible(true);

            // Call the method
            $method->invoke($controller, $mockUser);

            // Assert that the session was updated
            expect($_SESSION['user'])->toBe(789);
        });

        test('handles user with null ID gracefully', function (): void {
            $mockUser = $this->createMock(User::class);
            $mockUser->method('getId')->willReturn(null);

            $controller = new AuthController();

            // Use reflection to access the protected method
            $reflection = new ReflectionClass($controller);
            $method = $reflection->getMethod('saveUserSession');
            $method->setAccessible(true);

            // Call the method
            $method->invoke($controller, $mockUser);

            // Assert that the session was set to null
            expect($_SESSION['user'])->toBeNull();
        });
    });

    describe('method signatures and inheritance', function (): void {
        test('extends BaseController', function (): void {
            $controller = new AuthController();
            expect($controller)->toBeInstanceOf(\NickMous\Binsta\Internals\BaseController::class);
        });

        test('login method has correct signature', function (): void {
            $reflection = new ReflectionClass(AuthController::class);
            $method = $reflection->getMethod('login');

            expect($method->isPublic())->toBeTrue();
            expect($method->getNumberOfParameters())->toBe(1);

            $parameters = $method->getParameters();
            expect($parameters[0]->getName())->toBe('request');
            expect($parameters[0]->getType()?->__toString())->toBe(LoginRequest::class);
        });

        test('register method has correct signature', function (): void {
            $reflection = new ReflectionClass(AuthController::class);
            $method = $reflection->getMethod('register');

            expect($method->isPublic())->toBeTrue();
            expect($method->getNumberOfParameters())->toBe(1);

            $parameters = $method->getParameters();
            expect($parameters[0]->getName())->toBe('request');
            expect($parameters[0]->getType()?->__toString())->toBe(RegisterRequest::class);
        });

        test('saveUserSession method has correct signature', function (): void {
            $reflection = new ReflectionClass(AuthController::class);
            $method = $reflection->getMethod('saveUserSession');

            expect($method->isProtected())->toBeTrue();
            expect($method->getNumberOfParameters())->toBe(1);

            $parameters = $method->getParameters();
            expect($parameters[0]->getName())->toBe('user');
            expect($parameters[0]->getType()?->__toString())->toBe(User::class);
        });

        test('login method returns Response', function (): void {
            $reflection = new ReflectionClass(AuthController::class);
            $method = $reflection->getMethod('login');

            $returnType = $method->getReturnType();
            expect($returnType?->__toString())->toBe(\NickMous\Binsta\Internals\Response\Response::class);
        });

        test('register method returns Response', function (): void {
            $reflection = new ReflectionClass(AuthController::class);
            $method = $reflection->getMethod('register');

            $returnType = $method->getReturnType();
            expect($returnType?->__toString())->toBe(\NickMous\Binsta\Internals\Response\Response::class);
        });

        test('saveUserSession method returns void', function (): void {
            $reflection = new ReflectionClass(AuthController::class);
            $method = $reflection->getMethod('saveUserSession');

            $returnType = $method->getReturnType();
            expect($returnType?->__toString())->toBe('void');
        });
    });

    describe('class structure', function (): void {
        test('class exists and is instantiable', function (): void {
            $controller = new AuthController();
            expect($controller)->toBeInstanceOf(AuthController::class);
        });

        test('has expected public methods', function (): void {
            $reflection = new ReflectionClass(AuthController::class);
            $publicMethods = array_filter($reflection->getMethods(), fn($method) => $method->isPublic());
            $publicMethodNames = array_map(fn($method) => $method->getName(), $publicMethods);

            expect($publicMethodNames)->toContain('login');
            expect($publicMethodNames)->toContain('register');
        });

        test('has expected protected methods', function (): void {
            $reflection = new ReflectionClass(AuthController::class);
            $protectedMethods = array_filter($reflection->getMethods(), fn($method) => $method->isProtected());
            $protectedMethodNames = array_map(fn($method) => $method->getName(), $protectedMethods);

            expect($protectedMethodNames)->toContain('saveUserSession');
        });
    });
});
