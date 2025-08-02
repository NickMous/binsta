<?php

use NickMous\Binsta\Requests\Auth\RegisterRequest;
use NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException;

covers(RegisterRequest::class);

describe('RegisterRequest', function (): void {
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
        $request = new RegisterRequest();
        $rules = $request->rules();

        expect($rules)->toBe([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ]);
    });

    test('defines correct validation messages', function (): void {
        $request = new RegisterRequest();
        $messages = $request->messages();

        expect($messages)->toMatchArray([
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 8 characters.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.string' => 'Password confirmation must be a string.',
            'password_confirmation.same' => 'Password confirmation must match the password.',
        ]);
    });

    test('validates successfully with valid data', function (): void {
        // Mock valid request data
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Create request with mocked data
        $request = new class($requestData) extends RegisterRequest {
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

    test('fails validation when name is missing', function (): void {
        $requestData = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $request = new class($requestData) extends RegisterRequest {
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
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $request = new class($requestData) extends RegisterRequest {
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

    test('fails validation when password is too short', function (): void {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',  // less than 8 characters
            'password_confirmation' => '123'
        ];

        $request = new class($requestData) extends RegisterRequest {
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

    test('fails validation when passwords do not match', function (): void {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password'
        ];

        $request = new class($requestData) extends RegisterRequest {
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

    test('validates minimum password length correctly', function (): void {
        // Test exactly 8 characters (should pass)
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '12345678',  // exactly 8 characters
            'password_confirmation' => '12345678'
        ];

        $request = new class($requestData) extends RegisterRequest {
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

        expect(fn() => $request->validate())->not->toThrow(ValidationFailedException::class);

        // Test 7 characters (should fail)
        $requestData['password'] = '1234567';
        $requestData['password_confirmation'] = '1234567';
        
        $request = new class($requestData) extends RegisterRequest {
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

        $request = new class($requestData) extends RegisterRequest {
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