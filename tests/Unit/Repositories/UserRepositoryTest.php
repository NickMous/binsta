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
            $user = UserRepository::create('John Doe', 'johndoe', 'john@example.com', 'password123');
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

    describe('findByUsername', function (): void {
        test('returns user when found by username', function (): void {
            // Create a test user
            $user = UserRepository::create('John Doe', 'johndoe', 'john@example.com', 'password123');

            // Find the user by username
            $foundUser = UserRepository::findByUsername('johndoe');

            expect($foundUser)->not->toBeNull();
            expect($foundUser)->toBeInstanceOf(User::class);
            expect($foundUser->getId())->toBe($user->getId());
            expect($foundUser->name)->toBe('John Doe');
            expect($foundUser->username)->toBe('johndoe');
            expect($foundUser->email)->toBe('john@example.com');
        });

        test('returns null when username not found', function (): void {
            $foundUser = UserRepository::findByUsername('nonexistent');
            expect($foundUser)->toBeNull();
        });
    });

    describe('findByEmail', function (): void {
        test('returns user when found by email', function (): void {
            UserRepository::create('Jane Doe', 'janedoe', 'jane@example.com', 'password123');

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
            UserRepository::create('Test User', 'testuser', 'test@example.com', 'password123');

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
            $user = UserRepository::create('New User', 'newuser', 'new@example.com', 'password123');

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
            $user = UserRepository::create('Password User', 'pwduser', 'pwd@example.com', 'plaintext');

            // Password should be hashed, not plain text
            expect($user->password)->not->toBe('plaintext');
            expect($user->verifyPassword('plaintext'))->toBeTrue();
        });
    });

    describe('findByNameLike', function (): void {
        test('finds users with partial name match', function (): void {
            UserRepository::create('John Smith', 'johnsmith', 'john.smith@example.com', 'password123');
            UserRepository::create('John Doe', 'johndoe2', 'john.doe@example.com', 'password123');
            UserRepository::create('Jane Doe', 'janedoe2', 'jane.doe@example.com', 'password123');

            $users = UserRepository::findByNameLike('John');

            expect($users)->toHaveCount(2);
            expect($users[0]->name)->toMatch('/John/');
            expect($users[1]->name)->toMatch('/John/');
        });

        test('returns empty array when no matches', function (): void {
            UserRepository::create('Alice Cooper', 'alicecooper', 'alice@example.com', 'password123');

            $users = UserRepository::findByNameLike('Bob');

            expect($users)->toBeEmpty();
        });
    });

    describe('findAll', function (): void {
        test('returns all users ordered by created_at DESC', function (): void {
            $user1 = UserRepository::create('First User', 'firstuser', 'first@example.com', 'password123');
            // Wait a moment to ensure different timestamps
            sleep(1);
            $user2 = UserRepository::create('Second User', 'seconduser', 'second@example.com', 'password123');

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

            UserRepository::create('User 1', 'user1', 'user1@example.com', 'password123');
            expect(UserRepository::count())->toBe(1);

            UserRepository::create('User 2', 'user2', 'user2@example.com', 'password123');
            expect(UserRepository::count())->toBe(2);
        });
    });

    describe('findCreatedAfter', function (): void {
        test('finds users created after specified date', function (): void {
            // Create old user
            UserRepository::create('Old User', 'olduser', 'old@example.com', 'password123');

            // Wait a moment to ensure different timestamps
            sleep(1);
            $afterDate = new DateTime();

            // Wait another moment, then create new user
            sleep(1);
            UserRepository::create('New User', 'newuser2', 'new@example.com', 'password123');

            $users = UserRepository::findCreatedAfter($afterDate);

            expect($users)->toHaveCount(1);
            expect($users[0]->name)->toBe('New User');
        });

        test('returns empty array when no users created after date', function (): void {
            $futureDate = new DateTime('+1 year');

            UserRepository::create('Current User', 'currentuser', 'current@example.com', 'password123');

            $users = UserRepository::findCreatedAfter($futureDate);

            expect($users)->toBeEmpty();
        });
    });

    describe('update', function (): void {
        test('updates existing user fields', function (): void {
            $user = UserRepository::create('Original Name', 'originalname', 'original@example.com', 'password123');
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
            $user = UserRepository::create('User', 'user', 'user@example.com', 'oldpassword');
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
            $user = UserRepository::create('Original Name', 'originalname2', 'original2@example.com', 'password123');
            $userId = $user->getId();
            $originalEmail = $user->email;

            UserRepository::update($userId, ['name' => 'Updated Name']);

            $updatedUser = UserRepository::findById($userId);
            expect($updatedUser->name)->toBe('Updated Name');
            expect($updatedUser->email)->toBe($originalEmail); // Should remain unchanged
        });

        test('updates username field', function (): void {
            $user = UserRepository::create('User Name', 'oldusername', 'user@example.com', 'password123');
            $userId = $user->getId();

            $updatedUser = UserRepository::update($userId, [
                'username' => 'newusername'
            ]);

            expect($updatedUser)->not->toBeNull();
            expect($updatedUser->username)->toBe('newusername');

            // Verify changes persisted
            $foundUser = UserRepository::findById($userId);
            expect($foundUser->username)->toBe('newusername');
        });

        test('updates profile_picture field', function (): void {
            $user = UserRepository::create('User Name', 'username', 'user@example.com', 'password123');
            $userId = $user->getId();

            $updatedUser = UserRepository::update($userId, [
                'profile_picture' => 'avatar.jpg'
            ]);

            expect($updatedUser)->not->toBeNull();
            expect($updatedUser->profilePicture)->toBe('avatar.jpg');

            // Verify changes persisted
            $foundUser = UserRepository::findById($userId);
            expect($foundUser->profilePicture)->toBe('avatar.jpg');
        });
    });

    describe('deleteById', function (): void {
        test('deletes existing user and returns true', function (): void {
            $user = UserRepository::create('To Delete', 'todelete', 'delete@example.com', 'password123');
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
            UserRepository::create('Auth User', 'authuser', 'auth@example.com', 'correct-password');

            $user = UserRepository::authenticate('auth@example.com', 'correct-password');

            expect($user)->not->toBeNull();
            expect($user->name)->toBe('Auth User');
        });

        test('returns null when email not found', function (): void {
            $user = UserRepository::authenticate('nonexistent@example.com', 'any-password');

            expect($user)->toBeNull();
        });

        test('returns null when password is incorrect', function (): void {
            UserRepository::create('Auth User', 'authuser2', 'auth2@example.com', 'correct-password');

            $user = UserRepository::authenticate('auth2@example.com', 'wrong-password');

            expect($user)->toBeNull();
        });

        test('returns null when email exists but password is empty', function (): void {
            UserRepository::create('Auth User', 'authuser3', 'auth3@example.com', 'correct-password');

            $user = UserRepository::authenticate('auth3@example.com', '');

            expect($user)->toBeNull();
        });
    });

    describe('save', function (): void {
        test('saves user entity and returns it', function (): void {
            // Create a user
            $user = UserRepository::create('Test User', 'testuser', 'test@example.com', 'password123');

            // Modify the user
            $user->name = 'Modified Name';

            // Save the user
            $savedUser = UserRepository::save($user);

            expect($savedUser)->toBeInstanceOf(User::class);
            expect($savedUser->name)->toBe('Modified Name');

            // Verify changes persisted
            $foundUser = UserRepository::findById($user->getId());
            expect($foundUser->name)->toBe('Modified Name');
        });
    });
});
