<?php

namespace NickMous\Binsta\Entities;

use DateTime;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Traits\HasTimestamps;
use NickMous\Binsta\Repositories\UserRepository;

class User extends Entity
{
    use HasTimestamps;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public ?string $profilePicture = null;

    public ?string $biography = null;

    private bool $isHydrating = false;

    public string $password = '' {
        set {
    if ($this->isHydrating) {
        // During hydration, set raw hash without re-hashing
        $this->password = $value;
    } else {
        // Auto-hash password when setting normally
        $this->password = password_hash($value, PASSWORD_DEFAULT);
    }
        }
    }


    public static function getTableName(): string
    {
        return 'user';
    }

    /**
     * Create a new User instance
     */
    public static function create(string $name, string $username, string $email, string $password): self
    {
        $user = new self();
        $user->name = $name;
        $user->username = $username;
        $user->email = $email;
        $user->password = $password; // Will be auto-hashed by setter
        $user->createdAt = new DateTime();

        return $user;
    }

    /**
     * Set raw password hash (for hydration from database)
     */
    public function setPasswordHash(string $hash): void
    {
        $this->isHydrating = true;
        $this->password = $hash;
        $this->isHydrating = false;
    }

    /**
     * Verify a password against the user's hash
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Hydrate entity properties from the bean
     */
    protected function hydrate(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->isHydrating = true;

        $this->name = (string) $this->bean->name;
        $this->username = (string) $this->bean->username;
        $this->email = (string) $this->bean->email;
        $this->profilePicture = (string) $this->bean->profile_picture ?: null;
        $this->biography = $this->bean->biography ?: null;
        $this->password = (string) $this->bean->password; // Set raw hash during hydration

        $this->hydrateTimestamps();

        $this->isHydrating = false;
    }

    /**
     * Prepare the bean before saving
     */
    protected function prepare(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->bean->name = $this->name ?? '';
        $this->bean->username = $this->username ?? '';
        $this->bean->email = $this->email ?? '';
        $this->bean->profile_picture = (string) $this->profilePicture;
        $this->bean->biography = $this->biography ?? '';
        $this->bean->password = $this->password ?? '';

        $this->prepareTimestamps();
    }

    /**
     * Convert user to array (for JSON responses)
     * @return array<string, mixed>
     */
    public function toArray(bool $includePassword = false): array
    {
        $data = array_merge([
            'id' => $this->getId(),
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'profile_picture' => $this->profilePicture,
            'biography' => $this->biography,
        ], $this->getTimestampArray());

        if ($includePassword) {
            $data['password'] = $this->password;
        }

        return $data;
    }

    public function getRepository(): string
    {
        return UserRepository::class;
    }
}
