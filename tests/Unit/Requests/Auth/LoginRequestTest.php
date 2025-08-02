<?php

use NickMous\Binsta\Requests\Auth\LoginRequest;
use NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException;

covers(LoginRequest::class);

describe('LoginRequest', function (): void {
    beforeEach(function () {
        // Set up basic server environment for Request constructor
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
    });

    afterEach(function () {
        // Clean up
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['CONTENT_TYPE']);
    });

    test('defines correct validation rules', function (): void {
        $request = new LoginRequest();
        $rules = $request->rules();

        expect($rules)->toBe([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    });

    test('defines correct validation messages', function (): void {
        $request = new LoginRequest();
        $messages = $request->messages();

        expect($messages)->toMatchArray([
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
        ]);
    });

    test('validates successfully with valid data', function (): void {
        // Mock valid request data
        $requestData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        // Create request with mocked data
        $request = new class($requestData) extends LoginRequest {
            public function __construct(array $data) {
                // Skip parent constructor to avoid $_POST handling
                $this->parameters = $data;
            }
            
            private array $parameters;
            
            public function get(string $key, mixed $default = null): mixed {
                return $this->parameters[$key] ?? $default;
            }
            
            public function all(): array {
                return $this->parameters;
            }
        };

        // Should not throw exception
        expect(fn() => $request->validate())->not->toThrow(ValidationFailedException::class);
    });

    test('fails validation when email is missing', function (): void {
        $requestData = [
            'password' => 'password123'
        ];

        $request = new class($requestData) extends LoginRequest {
            public function __construct(array $data) {
                $this->parameters = $data;
            }
            
            private array $parameters;
            
            public function get(string $key, mixed $default = null): mixed {
                return $this->parameters[$key] ?? $default;
            }
            
            public function all(): array {
                return $this->parameters;
            }
        };

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('fails validation when password is missing', function (): void {
        $requestData = [
            'email' => 'john@example.com'
        ];

        $request = new class($requestData) extends LoginRequest {
            public function __construct(array $data) {
                $this->parameters = $data;
            }
            
            private array $parameters;
            
            public function get(string $key, mixed $default = null): mixed {
                return $this->parameters[$key] ?? $default;
            }
            
            public function all(): array {
                return $this->parameters;
            }
        };

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('fails validation when email is invalid', function (): void {
        $requestData = [
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $request = new class($requestData) extends LoginRequest {
            public function __construct(array $data) {
                $this->parameters = $data;
            }
            
            private array $parameters;
            
            public function get(string $key, mixed $default = null): mixed {
                return $this->parameters[$key] ?? $default;
            }
            
            public function all(): array {
                return $this->parameters;
            }
        };

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('validates all fields are required', function (): void {
        $requestData = []; // empty request

        $request = new class($requestData) extends LoginRequest {
            public function __construct(array $data) {
                $this->parameters = $data;
            }
            
            private array $parameters;
            
            public function get(string $key, mixed $default = null): mixed {
                return $this->parameters[$key] ?? $default;
            }
            
            public function all(): array {
                return $this->parameters;
            }
        };

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });
});