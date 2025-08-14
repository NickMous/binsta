<?php

namespace NickMous\Binsta\Repositories;

use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Repositories\BaseRepository;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class UserRepository extends BaseRepository
{
    /**
     * Find a user by ID
     */
    public function findById(int $id): ?User
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
    public function findByEmail(string $email): ?User
    {
        $bean = R::findOne(User::getTableName(), 'email = ?', [$email]);

        if ($bean === null) {
            return null;
        }

        return new User($bean);
    }

    /**
     * Find a user by username
     */
    public function findByUsername(string $username): ?User
    {
        $bean = R::findOne(User::getTableName(), 'username = ?', [$username]);

        if ($bean === null) {
            return null;
        }

        return new User($bean);
    }

    /**
     * Check if a user exists with the given email
     */
    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    /**
     * Create and save a new user
     */
    public function create(string $name, string $username, string $email, string $password): User
    {
        $user = User::create($name, $username, $email, $password);
        $user->save();

        return $user;
    }

    /**
     * Find users by name (partial match)
     * @return array<User>
     */
    public function findByNameLike(string $name): array
    {
        $beans = R::find(User::getTableName(), 'name LIKE ?', ['%' . $name . '%']);

        return array_values(array_map(fn(OODBBean $bean) => new User($bean), $beans));
    }

    /**
     * Get all users
     * @return array<User>
     */
    public function findAll(): array
    {
        $beans = R::find(User::getTableName(), 'ORDER BY created_at DESC');

        return array_values(array_map(fn(OODBBean $bean) => new User($bean), $beans));
    }

    /**
     * Count total users
     */
    public function count(): int
    {
        return R::count(User::getTableName());
    }

    /**
     * Find users created after a specific date
     * @return array<User>
     */
    public function findCreatedAfter(\DateTime $date): array
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
    public function update(int $id, array $data): ?User
    {
        $user = $this->findById($id);

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
    public function deleteById(int $id): bool
    {
        $user = $this->findById($id);

        if ($user === null) {
            return false;
        }

        $user->delete();
        return true;
    }

    /**
     * Save a user entity
     */
    public function save(User $user): User
    {
        $user->save();
        return $user;
    }

    /**
     * Authenticate user by email and password
     */
    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->findByEmail($email);

        if ($user === null || !$user->verifyPassword($password)) {
            return null;
        }

        return $user;
    }

    public function getEntityByParameter(string $parameterValue): Entity
    {
        if (is_numeric($parameterValue)) {
            $user = $this->findById((int)$parameterValue);
            if ($user !== null) {
                return $user;
            }
        }

        $user = $this->findByUsername($parameterValue) ?? $this->findByEmail($parameterValue);

        if ($user === null) {
            throw new \InvalidArgumentException("User not found for parameter: $parameterValue");
        }

        return $user;
    }

    /**
     * Search for users by name or username
     * @return User[]|null
     */
    public function searchFor(string $query): ?array
    {
        $query = '%' . $query . '%';
        $beans = R::find(User::getTableName(), 'name LIKE ? OR username LIKE ? OR email LIKE ? LIMIT 5', [$query, $query, $query]);

        return array_values(array_map(static fn(OODBBean $bean) => new User($bean), $beans));
    }
}
