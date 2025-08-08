<?php

use NickMous\Binsta\Requests\Auth\RegisterRequest;
use NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException;
use NickMous\Binsta\Managers\DatabaseManager;
use NickMous\Binsta\Kernel;

covers(RegisterRequest::class);

/**
 * Create a mocked RegisterRequest with given data for testing
 * @param array<string, mixed> $data
 * @return RegisterRequest
 */
function createMockedRegisterRequest(array $data): RegisterRequest
{
    return new class ($data) extends RegisterRequest {
        /** @param array<string, mixed> $data */
        public function __construct(array $data)
        {
            // Skip parent constructor to avoid $_POST handling
            $this->parameters = $data;
        }

        /** @var array<string, mixed> */
        private array $parameters;

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->parameters[$key] ?? $default;
        }

        public function all(): array
        {
            return $this->parameters;
        }
    };
}

describe('RegisterRequest', function (): void {
    beforeEach(function (): void {
        // Set up basic server environment for Request constructor
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        // Initialize database for feature tests
        new Kernel()->init();
        DatabaseManager::instantiate();
    });

    afterEach(function (): void {
        // Clean up database
        \RedBeanPHP\R::nuke();
        \RedBeanPHP\R::close();
        DatabaseManager::reset();

        // Clean up server environment
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['CONTENT_TYPE']);
    });

    test('defines correct validation rules', function (): void {
        $request = new RegisterRequest();
        $rules = $request->rules();

        expect($rules)->toBe([
            'name' => 'required|string',
            'username' => 'required|string|unique:user,username|regex:/^[a-zA-Z0-9_]+$/|min:3|max:20',
            'email' => 'required|email|unique:user,email',
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
            'username.required' => 'Username is required.',
            'username.string' => 'Username must be a string.',
            'username.unique' => 'Username is already taken.',
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username cannot exceed 20 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'Email address is already registered.',
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
            'username' => 'john_doe123',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $request = createMockedRegisterRequest($requestData);

        // Should not throw exception
        expect(fn() => $request->validate())->not->toThrow(ValidationFailedException::class);
    });

    test('fails validation when name is missing', function (): void {
        $requestData = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $request = createMockedRegisterRequest($requestData);

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

        $request = createMockedRegisterRequest($requestData);

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

        $request = createMockedRegisterRequest($requestData);

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

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('validates minimum password length correctly', function (): void {
        // Test exactly 8 characters (should pass)
        $requestData = [
            'name' => 'John Doe',
            'username' => 'john_doe123',
            'email' => 'john@example.com',
            'password' => '12345678',  // exactly 8 characters
            'password_confirmation' => '12345678'
        ];

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())->not->toThrow(ValidationFailedException::class);

        // Test 7 characters (should fail)
        $requestData['password'] = '1234567';
        $requestData['password_confirmation'] = '1234567';

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('validates all fields are required', function (): void {
        $requestData = []; // empty request

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('fails validation when username is missing', function (): void {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('fails validation when username is too short', function (): void {
        $requestData = [
            'name' => 'John Doe',
            'username' => 'ab', // less than 3 characters
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('fails validation when username is too long', function (): void {
        $requestData = [
            'name' => 'John Doe',
            'username' => 'this_username_is_way_too_long_and_exceeds_twenty_characters', // more than 20 characters
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('fails validation when username contains invalid characters', function (): void {
        $requestData = [
            'name' => 'John Doe',
            'username' => 'john-doe!', // contains dash and exclamation mark
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())
            ->toThrow(ValidationFailedException::class);
    });

    test('validates username length boundaries correctly', function (): void {
        // Test exactly 3 characters (should pass)
        $requestData = [
            'name' => 'John Doe',
            'username' => 'abc', // exactly 3 characters
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())->not->toThrow(ValidationFailedException::class);

        // Test exactly 20 characters (should pass)
        $requestData['username'] = 'abcdefghijklmnopqrst'; // exactly 20 characters

        $request = createMockedRegisterRequest($requestData);

        expect(fn() => $request->validate())->not->toThrow(ValidationFailedException::class);
    });

    test('validates username with valid characters', function (): void {
        $validUsernames = ['user123', 'test_user', 'User_Name_123', 'a', 'USERNAME'];

        foreach ($validUsernames as $username) {
            $requestData = [
                'name' => 'John Doe',
                'username' => $username,
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];

            $request = new class ($requestData) extends RegisterRequest {
                /** @param array<string, mixed> $data */
                public function __construct(array $data)
                {
                    $this->parameters = $data;
                }

                /** @var array<string, mixed> */
                private array $parameters;

                public function get(string $key, mixed $default = null): mixed
                {
                    return $this->parameters[$key] ?? $default;
                }

                public function all(): array
                {
                    return $this->parameters;
                }
            };

            expect(fn() => $request->validate())->not->toThrow(ValidationFailedException::class, "Username '{$username}' should be valid");
        }
    });

    test('transforms email to lowercase and trims whitespace', function (): void {
        $request = new RegisterRequest();

        $data = [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => '  John@EXAMPLE.COM  ',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $transformed = $request->transform($data);

        expect($transformed['email'])->toBe('john@example.com');
        expect($transformed['name'])->toBe('John Doe');
        expect($transformed['username'])->toBe('johndoe');
        expect($transformed['password'])->toBe('password123');
        expect($transformed['password_confirmation'])->toBe('password123');
    });

    test('handles missing email in transform', function (): void {
        $request = new RegisterRequest();

        $data = [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $transformed = $request->transform($data);

        expect($transformed)->toBe($data);
    });

    test('handles non-string email in transform', function (): void {
        $request = new RegisterRequest();

        $data = [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 123,
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $transformed = $request->transform($data);

        expect($transformed['email'])->toBe(123);
        expect($transformed['name'])->toBe('John Doe');
        expect($transformed['username'])->toBe('johndoe');
        expect($transformed['password'])->toBe('password123');
        expect($transformed['password_confirmation'])->toBe('password123');
    });
});
