<?php

namespace NickMous\Binsta\Repositories;

use NickMous\Binsta\Entities\User;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class UserRepository
{
    /**
     * Find a user by ID
     */
    public static function findById(int $id): ?User
    {
        $bean = R::load(User::getTableName(), $id);

        if ($bean->id == 0) {
            return null;
        }

        return new User($bean);
    }

    /**
     * Find a user by email
     */
    public static function findByEmail(string $email): ?User
    {
        $bean = R::findOne(User::getTableName(), 'email = ?', [$email]);

        if ($bean === null) {
            return null;
        }

        return new User($bean);
    }

    /**
     * Check if a user exists with the given email
     */
    public static function emailExists(string $email): bool
    {
        return self::findByEmail($email) !== null;
    }

    /**
     * Create and save a new user
     */
    public static function create(string $name, string $username, string $email, string $password): User
    {
        $user = User::create($name, $username, $email, $password);
        $user->save();

        return $user;
    }

    /**
     * Find users by name (partial match)
     * @return array<User>
     */
    public static function findByNameLike(string $name): array
    {
        $beans = R::find(User::getTableName(), 'name LIKE ?', ['%' . $name . '%']);

        return array_values(array_map(fn(OODBBean $bean) => new User($bean), $beans));
    }

    /**
     * Get all users
     * @return array<User>
     */
    public static function findAll(): array
    {
        $beans = R::find(User::getTableName(), 'ORDER BY created_at DESC');

        return array_values(array_map(fn(OODBBean $bean) => new User($bean), $beans));
    }

    /**
     * Count total users
     */
    public static function count(): int
    {
        return R::count(User::getTableName());
    }

    /**
     * Find users created after a specific date
     * @return array<User>
     */
    public static function findCreatedAfter(\DateTime $date): array
    {
        $beans = R::find(
            User::getTableName(),
            'created_at > ?',
            [$date->format('Y-m-d H:i:s')]
        );

        return array_values(array_map(fn(OODBBean $bean) => new User($bean), $beans));
    }

    /**
     * Update user by ID
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): ?User
    {
        $user = self::findById($id);

        if ($user === null) {
            return null;
        }

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['username'])) {
            $user->username = $data['username'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (isset($data['profile_picture'])) {
            $user->profilePicture = $data['profile_picture'];
        }

        if (isset($data['password'])) {
            $user->password = $data['password']; // Will be auto-hashed
        }

        $user->save();

        return $user;
    }

    /**
     * Delete user by ID
     */
    public static function deleteById(int $id): bool
    {
        $user = self::findById($id);

        if ($user === null) {
            return false;
        }

        $user->delete();
        return true;
    }

    /**
     * Authenticate user by email and password
     */
    public static function authenticate(string $email, string $password): ?User
    {
        $user = self::findByEmail($email);

        if ($user === null || !$user->verifyPassword($password)) {
            return null;
        }

        return $user;
    }
}
