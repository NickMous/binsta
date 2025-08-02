<?php

use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Kernel;
use RedBeanPHP\OODBBean;

covers(User::class);

describe('User', function (): void {
    beforeEach(function (): void {
        // Initialize the kernel for database connection
        (new Kernel())->init();

        // Clear any existing test data
        \RedBeanPHP\R::wipe('user');
    });

    afterEach(function (): void {
        // Clean up after each test
        \RedBeanPHP\R::wipe('user');
    });

    describe('static methods', function (): void {
        test('getTableName returns correct table name', function (): void {
            expect(User::getTableName())->toBe('user');
        });

        test('create method creates new user with hashed password', function (): void {
            $user = User::create('John Doe', 'john@example.com', 'password123');

            expect($user)->toBeInstanceOf(User::class);
            expect($user->name)->toBe('John Doe');
            expect($user->email)->toBe('john@example.com');
            expect($user->password)->not->toBe('password123'); // Should be hashed
            expect($user->verifyPassword('password123'))->toBeTrue();
            expect($user->createdAt)->toBeInstanceOf(DateTime::class);
            expect($user->getId())->toBeNull(); // Not saved yet
        });

        test('create method sets created_at timestamp', function (): void {
            $beforeCreate = new DateTime();
            $user = User::create('Jane Doe', 'jane@example.com', 'password123');
            $afterCreate = new DateTime();

            expect($user->createdAt)->toBeInstanceOf(DateTime::class);
            expect($user->createdAt->getTimestamp())->toBeGreaterThanOrEqual($beforeCreate->getTimestamp());
            expect($user->createdAt->getTimestamp())->toBeLessThanOrEqual($afterCreate->getTimestamp());
        });
    });

    describe('property access', function (): void {
        test('name property can be set and retrieved', function (): void {
            $user = new User();

            expect($user->name)->toBe(''); // Default empty string

            $user->name = 'Test User';
            expect($user->name)->toBe('Test User');
        });

        test('email property can be set and retrieved', function (): void {
            $user = new User();

            expect($user->email)->toBe(''); // Default empty string

            $user->email = 'test@example.com';
            expect($user->email)->toBe('test@example.com');
        });

        test('password property auto-hashes when set normally', function (): void {
            $user = new User();

            $user->password = 'plaintext123';

            expect($user->password)->not->toBe('plaintext123');
            expect($user->verifyPassword('plaintext123'))->toBeTrue();
            expect($user->verifyPassword('wrongpassword'))->toBeFalse();
        });

        test('createdAt property can be set and retrieved', function (): void {
            $user = new User();
            $date = new DateTime('2024-01-01 12:00:00');

            expect($user->createdAt)->toBeNull(); // Default null

            $user->createdAt = $date;
            expect($user->createdAt)->toBe($date);
        });

        test('updatedAt property can be set and retrieved', function (): void {
            $user = new User();
            $date = new DateTime('2024-01-01 12:00:00');

            expect($user->updatedAt)->toBeNull(); // Default null

            $user->updatedAt = $date;
            expect($user->updatedAt)->toBe($date);
        });
    });

    describe('password handling', function (): void {
        test('verifyPassword works with correct password', function (): void {
            $user = User::create('Test User', 'test@example.com', 'secret123');

            expect($user->verifyPassword('secret123'))->toBeTrue();
        });

        test('verifyPassword fails with incorrect password', function (): void {
            $user = User::create('Test User', 'test@example.com', 'secret123');

            expect($user->verifyPassword('wrongpassword'))->toBeFalse();
        });

        test('verifyPassword fails with empty password', function (): void {
            $user = User::create('Test User', 'test@example.com', 'secret123');

            expect($user->verifyPassword(''))->toBeFalse();
        });

        test('setPasswordHash sets raw hash without re-hashing', function (): void {
            $user = new User();
            $rawHash = '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890';

            $user->setPasswordHash($rawHash);

            expect($user->password)->toBe($rawHash);
        });

        test('password is re-hashed when changed after creation', function (): void {
            $user = User::create('Test User', 'test@example.com', 'original123');
            $originalHash = $user->password;

            $user->password = 'newpassword456';

            expect($user->password)->not->toBe($originalHash);
            expect($user->verifyPassword('newpassword456'))->toBeTrue();
            expect($user->verifyPassword('original123'))->toBeFalse();
        });
    });

    describe('toArray method', function (): void {
        test('toArray returns correct data without password', function (): void {
            $user = User::create('John Doe', 'john@example.com', 'password123');
            $user->save(); // Save to get an ID

            $array = $user->toArray();

            expect($array)->toHaveKey('id');
            expect($array)->toHaveKey('name');
            expect($array)->toHaveKey('email');
            expect($array)->toHaveKey('created_at');
            expect($array)->toHaveKey('updated_at');
            expect($array)->not->toHaveKey('password');

            expect($array['name'])->toBe('John Doe');
            expect($array['email'])->toBe('john@example.com');
            expect($array['id'])->not->toBeNull();
        });

        test('toArray includes password when requested', function (): void {
            $user = User::create('John Doe', 'john@example.com', 'password123');

            $array = $user->toArray(true);

            expect($array)->toHaveKey('password');
            expect($array['password'])->toBe($user->password);
        });

        test('toArray formats timestamps correctly', function (): void {
            $user = User::create('John Doe', 'john@example.com', 'password123');

            $array = $user->toArray();

            if ($array['created_at'] !== null) {
                expect($array['created_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
            }
        });

        test('toArray handles null timestamps', function (): void {
            $user = new User();
            $user->name = 'Test User';
            $user->email = 'test@example.com';

            $array = $user->toArray();

            expect($array['created_at'])->toBeNull();
            expect($array['updated_at'])->toBeNull();
        });
    });

    describe('database operations', function (): void {
        test('save and retrieve user maintains data integrity', function (): void {
            $user = User::create('Save Test', 'save@example.com', 'password123');
            $originalPassword = $user->password;

            $userId = $user->save();

            expect($userId)->toBeInt();
            expect($user->getId())->toBe($userId);
            expect($user->exists())->toBeTrue();

            // Retrieve from database to test hydration
            $retrievedUser = new User(\RedBeanPHP\R::load('user', $userId));

            expect($retrievedUser->name)->toBe('Save Test');
            expect($retrievedUser->email)->toBe('save@example.com');
            expect($retrievedUser->password)->toBe($originalPassword);
            expect($retrievedUser->verifyPassword('password123'))->toBeTrue();
            expect($retrievedUser->createdAt)->toBeInstanceOf(DateTime::class);
        });

        test('prepare method sets updated_at on save', function (): void {
            $user = User::create('Update Test', 'update@example.com', 'password123');

            expect($user->updatedAt)->toBeNull(); // Not set initially

            $user->save();

            expect($user->updatedAt)->toBeInstanceOf(DateTime::class);
        });

        test('prepare method sets created_at for new records without one', function (): void {
            $user = new User();
            $user->name = 'No Timestamp';
            $user->email = 'no@example.com';
            $user->password = 'password123';

            expect($user->createdAt)->toBeNull();

            $user->save();

            expect($user->createdAt)->toBeInstanceOf(DateTime::class);
        });

        test('hydration preserves password hash without re-hashing', function (): void {
            // Create and save user
            $user = User::create('Hydration Test', 'hydration@example.com', 'password123');
            $originalHash = $user->password;
            $user->save();

            // Create new instance from database
            $bean = \RedBeanPHP\R::load('user', $user->getId());
            $hydratedUser = new User($bean);

            expect($hydratedUser->password)->toBe($originalHash);
            expect($hydratedUser->verifyPassword('password123'))->toBeTrue();
        });

        test('delete removes user from database', function (): void {
            $user = User::create('Delete Test', 'delete@example.com', 'password123');
            $user->save();
            $userId = $user->getId();

            expect($user->exists())->toBeTrue();

            $user->delete();

            expect($user->exists())->toBeFalse();

            // Verify user is gone from database
            $bean = \RedBeanPHP\R::load('user', $userId);
            expect($bean->id)->toBe(0); // RedBean returns empty bean with id=0 for non-existent records
        });
    });

    describe('edge cases', function (): void {
        test('hydrate handles empty bean data gracefully', function (): void {
            $bean = \RedBeanPHP\R::dispense('user');
            // Don't set any properties on the bean

            $user = new User($bean);

            expect($user->name)->toBe('');
            expect($user->email)->toBe('');
            expect($user->password)->toBe('');
            expect($user->createdAt)->toBeNull();
            expect($user->updatedAt)->toBeNull();
        });

        test('hydrate handles invalid date strings gracefully', function (): void {
            $bean = \RedBeanPHP\R::dispense('user');
            $bean->name = 'Test User';
            $bean->email = 'test@example.com';
            $bean->password = 'hash123';
            $bean->created_at = ''; // Empty date string
            $bean->updated_at = ''; // Empty date string

            $user = new User($bean);

            expect($user->createdAt)->toBeNull();
            expect($user->updatedAt)->toBeNull();
        });

        test('prepare handles null bean gracefully', function (): void {
            $user = new User();
            $user->name = 'Test User';

            // This should not throw an error
            $reflection = new ReflectionClass($user);
            $method = $reflection->getMethod('prepare');
            $method->setAccessible(true);
            $method->invoke($user);

            // Should pass without error
            expect(true)->toBeTrue();
        });

        test('hydrate handles null bean gracefully', function (): void {
            $user = new User();

            // This should not throw an error
            $reflection = new ReflectionClass($user);
            $method = $reflection->getMethod('hydrate');
            $method->setAccessible(true);
            $method->invoke($user);

            // Should pass without error
            expect(true)->toBeTrue();
        });
    });
});
