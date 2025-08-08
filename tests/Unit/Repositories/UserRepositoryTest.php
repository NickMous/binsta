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

        $this->userRepository = new UserRepository();
    });

    afterEach(function (): void {
        // Clean up after each test
        \RedBeanPHP\R::wipe('user');
    });

    describe('findById', function (): void {
        test('returns user when found', function (): void {
            // Create a test user
            $user = $this->userRepository->create('John Doe', 'johndoe', 'john@example.com', 'password123');
            $userId = $user->getId();

            // Find the user by ID
            $foundUser = $this->userRepository->findById($userId);

            expect($foundUser)->not->toBeNull();
            expect($foundUser->name)->toBe('John Doe');
            expect($foundUser->email)->toBe('john@example.com');
        });

        test('returns null when user not found', function (): void {
            $foundUser = $this->userRepository->findById(999999);

            expect($foundUser)->toBeNull();
        });

        test('returns null for non-existent ID', function (): void {
            $foundUser = $this->userRepository->findById(0);

            expect($foundUser)->toBeNull();
        });
    });

    describe('findByUsername', function (): void {
        test('returns user when found by username', function (): void {
            // Create a test user
            $user = $this->userRepository->create('John Doe', 'johndoe', 'john@example.com', 'password123');

            // Find the user by username
            $foundUser = $this->userRepository->findByUsername('johndoe');

            expect($foundUser)->not->toBeNull();
            expect($foundUser)->toBeInstanceOf(User::class);
            expect($foundUser->getId())->toBe($user->getId());
            expect($foundUser->name)->toBe('John Doe');
            expect($foundUser->username)->toBe('johndoe');
            expect($foundUser->email)->toBe('john@example.com');
        });

        test('returns null when username not found', function (): void {
            $foundUser = $this->userRepository->findByUsername('nonexistent');
            expect($foundUser)->toBeNull();
        });
    });

    describe('findByEmail', function (): void {
        test('returns user when found by email', function (): void {
            $this->userRepository->create('Jane Doe', 'janedoe', 'jane@example.com', 'password123');

            $foundUser = $this->userRepository->findByEmail('jane@example.com');

            expect($foundUser)->not->toBeNull();
            expect($foundUser->name)->toBe('Jane Doe');
            expect($foundUser->email)->toBe('jane@example.com');
        });

        test('returns null when email not found', function (): void {
            $foundUser = $this->userRepository->findByEmail('nonexistent@example.com');

            expect($foundUser)->toBeNull();
        });
    });

    describe('emailExists', function (): void {
        test('returns true when email exists', function (): void {
            $this->userRepository->create('Test User', 'testuser', 'test@example.com', 'password123');

            $exists = $this->userRepository->emailExists('test@example.com');

            expect($exists)->toBeTrue();
        });

        test('returns false when email does not exist', function (): void {
            $exists = $this->userRepository->emailExists('nonexistent@example.com');

            expect($exists)->toBeFalse();
        });
    });

    describe('create', function (): void {
        test('creates and saves a new user', function (): void {
            $user = $this->userRepository->create('New User', 'newuser', 'new@example.com', 'password123');

            expect($user)->toBeInstanceOf(User::class);
            expect($user->getId())->not->toBeNull();
            expect($user->name)->toBe('New User');
            expect($user->email)->toBe('new@example.com');
            expect($user->createdAt)->not->toBeNull();

            // Verify it's actually saved to database
            $foundUser = $this->userRepository->findByEmail('new@example.com');
            expect($foundUser)->not->toBeNull();
        });

        test('automatically hashes password', function (): void {
            $user = $this->userRepository->create('Password User', 'pwduser', 'pwd@example.com', 'plaintext');

            // Password should be hashed, not plain text
            expect($user->password)->not->toBe('plaintext');
            expect($user->verifyPassword('plaintext'))->toBeTrue();
        });
    });

    describe('findByNameLike', function (): void {
        test('finds users with partial name match', function (): void {
            $this->userRepository->create('John Smith', 'johnsmith', 'john.smith@example.com', 'password123');
            $this->userRepository->create('John Doe', 'johndoe2', 'john.doe@example.com', 'password123');
            $this->userRepository->create('Jane Doe', 'janedoe2', 'jane.doe@example.com', 'password123');

            $users = $this->userRepository->findByNameLike('John');

            expect($users)->toHaveCount(2);
            expect($users[0]->name)->toMatch('/John/');
            expect($users[1]->name)->toMatch('/John/');
        });

        test('returns empty array when no matches', function (): void {
            $this->userRepository->create('Alice Cooper', 'alicecooper', 'alice@example.com', 'password123');

            $users = $this->userRepository->findByNameLike('Bob');

            expect($users)->toBeEmpty();
        });
    });

    describe('findAll', function (): void {
        test('returns all users ordered by created_at DESC', function (): void {
            $user1 = $this->userRepository->create('First User', 'firstuser', 'first@example.com', 'password123');
            $user2 = $this->userRepository->create('Second User', 'seconduser', 'second@example.com', 'password123');

            // Set explicit timestamps to ensure proper ordering without using sleep()
            $user1->createdAt = new DateTime('2023-01-01 10:00:00');
            $user2->createdAt = new DateTime('2023-01-01 11:00:00');

            // Save the updated timestamps
            $this->userRepository->save($user1);
            $this->userRepository->save($user2);

            $users = $this->userRepository->findAll();

            expect($users)->toHaveCount(2);
            // Should be ordered by created_at DESC (newest first)
            expect($users[0]->name)->toBe('Second User');
            expect($users[1]->name)->toBe('First User');
        });


        test('returns empty array when no users exist', function (): void {
            $users = $this->userRepository->findAll();

            expect($users)->toBeEmpty();
        });
    });

    describe('count', function (): void {
        test('returns correct count of users', function (): void {
            expect($this->userRepository->count())->toBe(0);

            $this->userRepository->create('User 1', 'user1', 'user1@example.com', 'password123');
            expect($this->userRepository->count())->toBe(1);

            $this->userRepository->create('User 2', 'user2', 'user2@example.com', 'password123');
            expect($this->userRepository->count())->toBe(2);
        });
    });

    describe('findCreatedAfter', function (): void {
        test('finds users created after specified date', function (): void {
            // Create old user with explicit timestamp
            $oldUser = $this->userRepository->create('Old User', 'olduser', 'old@example.com', 'password123');
            $oldUser->createdAt = new DateTime('2023-01-01 10:00:00');
            $this->userRepository->save($oldUser);

            // Define a cutoff date between old and new users
            $afterDate = new DateTime('2023-01-01 10:30:00');

            // Create new user with timestamp after the cutoff
            $newUser = $this->userRepository->create('New User', 'newuser2', 'new@example.com', 'password123');
            $newUser->createdAt = new DateTime('2023-01-01 11:00:00');
            $this->userRepository->save($newUser);

            $users = $this->userRepository->findCreatedAfter($afterDate);

            expect($users)->toHaveCount(1);
            expect($users[0]->name)->toBe('New User');
        });

        test('returns empty array when no users created after date', function (): void {
            $futureDate = new DateTime('+1 year');

            $this->userRepository->create('Current User', 'currentuser', 'current@example.com', 'password123');

            $users = $this->userRepository->findCreatedAfter($futureDate);

            expect($users)->toBeEmpty();
        });
    });

    describe('update', function (): void {
        test('updates existing user fields', function (): void {
            $user = $this->userRepository->create('Original Name', 'originalname', 'original@example.com', 'password123');
            $userId = $user->getId();

            $updatedUser = $this->userRepository->update($userId, [
                'name' => 'Updated Name',
                'email' => 'updated@example.com'
            ]);

            expect($updatedUser)->not->toBeNull();
            expect($updatedUser->name)->toBe('Updated Name');
            expect($updatedUser->email)->toBe('updated@example.com');

            // Verify changes persisted
            $foundUser = $this->userRepository->findById($userId);
            expect($foundUser->name)->toBe('Updated Name');
        });

        test('updates password and hashes it', function (): void {
            $user = $this->userRepository->create('User', 'user', 'user@example.com', 'oldpassword');
            $userId = $user->getId();

            $updatedUser = $this->userRepository->update($userId, [
                'password' => 'newpassword'
            ]);

            expect($updatedUser->verifyPassword('newpassword'))->toBeTrue();
            expect($updatedUser->verifyPassword('oldpassword'))->toBeFalse();
        });

        test('returns null when user not found', function (): void {
            $result = $this->userRepository->update(999999, ['name' => 'New Name']);

            expect($result)->toBeNull();
        });

        test('only updates provided fields', function (): void {
            $user = $this->userRepository->create('Original Name', 'originalname2', 'original2@example.com', 'password123');
            $userId = $user->getId();
            $originalEmail = $user->email;

            $this->userRepository->update($userId, ['name' => 'Updated Name']);

            $updatedUser = $this->userRepository->findById($userId);
            expect($updatedUser->name)->toBe('Updated Name');
            expect($updatedUser->email)->toBe($originalEmail); // Should remain unchanged
        });

        test('updates username field', function (): void {
            $user = $this->userRepository->create('User Name', 'oldusername', 'user@example.com', 'password123');
            $userId = $user->getId();

            $updatedUser = $this->userRepository->update($userId, [
                'username' => 'newusername'
            ]);

            expect($updatedUser)->not->toBeNull();
            expect($updatedUser->username)->toBe('newusername');

            // Verify changes persisted
            $foundUser = $this->userRepository->findById($userId);
            expect($foundUser->username)->toBe('newusername');
        });

        test('updates profile_picture field', function (): void {
            $user = $this->userRepository->create('User Name', 'username', 'user@example.com', 'password123');
            $userId = $user->getId();

            $updatedUser = $this->userRepository->update($userId, [
                'profile_picture' => 'avatar.jpg'
            ]);

            expect($updatedUser)->not->toBeNull();
            expect($updatedUser->profilePicture)->toBe('avatar.jpg');

            // Verify changes persisted
            $foundUser = $this->userRepository->findById($userId);
            expect($foundUser->profilePicture)->toBe('avatar.jpg');
        });
    });

    describe('deleteById', function (): void {
        test('deletes existing user and returns true', function (): void {
            $user = $this->userRepository->create('To Delete', 'todelete', 'delete@example.com', 'password123');
            $userId = $user->getId();

            $result = $this->userRepository->deleteById($userId);

            expect($result)->toBeTrue();
            expect($this->userRepository->findById($userId))->toBeNull();
        });

        test('returns false when user not found', function (): void {
            $result = $this->userRepository->deleteById(999999);

            expect($result)->toBeFalse();
        });
    });

    describe('authenticate', function (): void {
        test('returns user when credentials are correct', function (): void {
            $this->userRepository->create('Auth User', 'authuser', 'auth@example.com', 'correct-password');

            $user = $this->userRepository->authenticate('auth@example.com', 'correct-password');

            expect($user)->not->toBeNull();
            expect($user->name)->toBe('Auth User');
        });

        test('returns null when email not found', function (): void {
            $user = $this->userRepository->authenticate('nonexistent@example.com', 'any-password');

            expect($user)->toBeNull();
        });

        test('returns null when password is incorrect', function (): void {
            $this->userRepository->create('Auth User', 'authuser2', 'auth2@example.com', 'correct-password');

            $user = $this->userRepository->authenticate('auth2@example.com', 'wrong-password');

            expect($user)->toBeNull();
        });

        test('returns null when email exists but password is empty', function (): void {
            $this->userRepository->create('Auth User', 'authuser3', 'auth3@example.com', 'correct-password');

            $user = $this->userRepository->authenticate('auth3@example.com', '');

            expect($user)->toBeNull();
        });
    });

    describe('save', function (): void {
        test('saves user entity and returns it', function (): void {
            // Create a user
            $user = $this->userRepository->create('Test User', 'testuser', 'test@example.com', 'password123');

            // Modify the user
            $user->name = 'Modified Name';

            // Save the user
            $savedUser = $this->userRepository->save($user);

            expect($savedUser)->toBeInstanceOf(User::class);
            expect($savedUser->name)->toBe('Modified Name');

            // Verify changes persisted
            $foundUser = $this->userRepository->findById($user->getId());
            expect($foundUser->name)->toBe('Modified Name');
        });
    });
});
