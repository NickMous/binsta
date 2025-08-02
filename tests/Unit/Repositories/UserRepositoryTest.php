<?php

use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Repositories\UserRepository;
use NickMous\Binsta\Kernel;

covers(UserRepository::class);

describe('UserRepository', function (): void {
    beforeEach(function (): void {
        // Initialize the kernel for database connection
        new Kernel()->init();

        // Clear any existing test data
        \RedBeanPHP\R::wipe('user');
    });

    afterEach(function (): void {
        // Clean up after each test
        \RedBeanPHP\R::wipe('user');
    });

    describe('findById', function (): void {
        test('returns user when found', function (): void {
            // Create a test user
            $user = UserRepository::create('John Doe', 'john@example.com', 'password123');
            $userId = $user->getId();

            // Find the user by ID
            $foundUser = UserRepository::findById($userId);

            expect($foundUser)->not->toBeNull();
            expect($foundUser->name)->toBe('John Doe');
            expect($foundUser->email)->toBe('john@example.com');
        });

        test('returns null when user not found', function (): void {
            $foundUser = UserRepository::findById(999999);

            expect($foundUser)->toBeNull();
        });

        test('returns null for non-existent ID', function (): void {
            $foundUser = UserRepository::findById(0);

            expect($foundUser)->toBeNull();
        });
    });

    describe('findByEmail', function (): void {
        test('returns user when found by email', function (): void {
            UserRepository::create('Jane Doe', 'jane@example.com', 'password123');

            $foundUser = UserRepository::findByEmail('jane@example.com');

            expect($foundUser)->not->toBeNull();
            expect($foundUser->name)->toBe('Jane Doe');
            expect($foundUser->email)->toBe('jane@example.com');
        });

        test('returns null when email not found', function (): void {
            $foundUser = UserRepository::findByEmail('nonexistent@example.com');

            expect($foundUser)->toBeNull();
        });
    });

    describe('emailExists', function (): void {
        test('returns true when email exists', function (): void {
            UserRepository::create('Test User', 'test@example.com', 'password123');

            $exists = UserRepository::emailExists('test@example.com');

            expect($exists)->toBeTrue();
        });

        test('returns false when email does not exist', function (): void {
            $exists = UserRepository::emailExists('nonexistent@example.com');

            expect($exists)->toBeFalse();
        });
    });

    describe('create', function (): void {
        test('creates and saves a new user', function (): void {
            $user = UserRepository::create('New User', 'new@example.com', 'password123');

            expect($user)->toBeInstanceOf(User::class);
            expect($user->getId())->not->toBeNull();
            expect($user->name)->toBe('New User');
            expect($user->email)->toBe('new@example.com');
            expect($user->createdAt)->not->toBeNull();

            // Verify it's actually saved to database
            $foundUser = UserRepository::findByEmail('new@example.com');
            expect($foundUser)->not->toBeNull();
        });

        test('automatically hashes password', function (): void {
            $user = UserRepository::create('Password User', 'pwd@example.com', 'plaintext');

            // Password should be hashed, not plain text
            expect($user->password)->not->toBe('plaintext');
            expect($user->verifyPassword('plaintext'))->toBeTrue();
        });
    });

    describe('findByNameLike', function (): void {
        test('finds users with partial name match', function (): void {
            UserRepository::create('John Smith', 'john.smith@example.com', 'password123');
            UserRepository::create('John Doe', 'john.doe@example.com', 'password123');
            UserRepository::create('Jane Doe', 'jane.doe@example.com', 'password123');

            $users = UserRepository::findByNameLike('John');

            expect($users)->toHaveCount(2);
            expect($users[0]->name)->toMatch('/John/');
            expect($users[1]->name)->toMatch('/John/');
        });

        test('returns empty array when no matches', function (): void {
            UserRepository::create('Alice Cooper', 'alice@example.com', 'password123');

            $users = UserRepository::findByNameLike('Bob');

            expect($users)->toBeEmpty();
        });
    });

    describe('findAll', function (): void {
        test('returns all users ordered by created_at DESC', function (): void {
            $user1 = UserRepository::create('First User', 'first@example.com', 'password123');
            // Wait a moment to ensure different timestamps
            sleep(1);
            $user2 = UserRepository::create('Second User', 'second@example.com', 'password123');

            $users = UserRepository::findAll();

            expect($users)->toHaveCount(2);
            // Should be ordered by created_at DESC (newest first)
            expect($users[0]->name)->toBe('Second User');
            expect($users[1]->name)->toBe('First User');
        });


        test('returns empty array when no users exist', function (): void {
            $users = UserRepository::findAll();

            expect($users)->toBeEmpty();
        });
    });

    describe('count', function (): void {
        test('returns correct count of users', function (): void {
            expect(UserRepository::count())->toBe(0);

            UserRepository::create('User 1', 'user1@example.com', 'password123');
            expect(UserRepository::count())->toBe(1);

            UserRepository::create('User 2', 'user2@example.com', 'password123');
            expect(UserRepository::count())->toBe(2);
        });
    });

    describe('findCreatedAfter', function (): void {
        test('finds users created after specified date', function (): void {
            // Create old user
            UserRepository::create('Old User', 'old@example.com', 'password123');

            // Wait a moment to ensure different timestamps
            sleep(1);
            $afterDate = new DateTime();

            // Wait another moment, then create new user
            sleep(1);
            UserRepository::create('New User', 'new@example.com', 'password123');

            $users = UserRepository::findCreatedAfter($afterDate);

            expect($users)->toHaveCount(1);
            expect($users[0]->name)->toBe('New User');
        });

        test('returns empty array when no users created after date', function (): void {
            $futureDate = new DateTime('+1 year');

            UserRepository::create('Current User', 'current@example.com', 'password123');

            $users = UserRepository::findCreatedAfter($futureDate);

            expect($users)->toBeEmpty();
        });
    });

    describe('update', function (): void {
        test('updates existing user fields', function (): void {
            $user = UserRepository::create('Original Name', 'original@example.com', 'password123');
            $userId = $user->getId();

            $updatedUser = UserRepository::update($userId, [
                'name' => 'Updated Name',
                'email' => 'updated@example.com'
            ]);

            expect($updatedUser)->not->toBeNull();
            expect($updatedUser->name)->toBe('Updated Name');
            expect($updatedUser->email)->toBe('updated@example.com');

            // Verify changes persisted
            $foundUser = UserRepository::findById($userId);
            expect($foundUser->name)->toBe('Updated Name');
        });

        test('updates password and hashes it', function (): void {
            $user = UserRepository::create('User', 'user@example.com', 'oldpassword');
            $userId = $user->getId();

            $updatedUser = UserRepository::update($userId, [
                'password' => 'newpassword'
            ]);

            expect($updatedUser->verifyPassword('newpassword'))->toBeTrue();
            expect($updatedUser->verifyPassword('oldpassword'))->toBeFalse();
        });

        test('returns null when user not found', function (): void {
            $result = UserRepository::update(999999, ['name' => 'New Name']);

            expect($result)->toBeNull();
        });

        test('only updates provided fields', function (): void {
            $user = UserRepository::create('Original Name', 'original@example.com', 'password123');
            $userId = $user->getId();
            $originalEmail = $user->email;

            UserRepository::update($userId, ['name' => 'Updated Name']);

            $updatedUser = UserRepository::findById($userId);
            expect($updatedUser->name)->toBe('Updated Name');
            expect($updatedUser->email)->toBe($originalEmail); // Should remain unchanged
        });
    });

    describe('deleteById', function (): void {
        test('deletes existing user and returns true', function (): void {
            $user = UserRepository::create('To Delete', 'delete@example.com', 'password123');
            $userId = $user->getId();

            $result = UserRepository::deleteById($userId);

            expect($result)->toBeTrue();
            expect(UserRepository::findById($userId))->toBeNull();
        });

        test('returns false when user not found', function (): void {
            $result = UserRepository::deleteById(999999);

            expect($result)->toBeFalse();
        });
    });

    describe('authenticate', function (): void {
        test('returns user when credentials are correct', function (): void {
            UserRepository::create('Auth User', 'auth@example.com', 'correct-password');

            $user = UserRepository::authenticate('auth@example.com', 'correct-password');

            expect($user)->not->toBeNull();
            expect($user->name)->toBe('Auth User');
        });

        test('returns null when email not found', function (): void {
            $user = UserRepository::authenticate('nonexistent@example.com', 'any-password');

            expect($user)->toBeNull();
        });

        test('returns null when password is incorrect', function (): void {
            UserRepository::create('Auth User', 'auth@example.com', 'correct-password');

            $user = UserRepository::authenticate('auth@example.com', 'wrong-password');

            expect($user)->toBeNull();
        });

        test('returns null when email exists but password is empty', function (): void {
            UserRepository::create('Auth User', 'auth@example.com', 'correct-password');

            $user = UserRepository::authenticate('auth@example.com', '');

            expect($user)->toBeNull();
        });
    });
});
