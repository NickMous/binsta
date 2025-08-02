<?php

namespace NickMous\Binsta\Entities;

use DateTime;
use NickMous\Binsta\Internals\Entities\Entity;
use RedBeanPHP\OODBBean;

class User extends Entity
{
    public string $name {
        get => $this->name ?? '';
        set => $this->name = $value;
    }

    public string $email {
        get => $this->email ?? '';
        set => $this->email = $value;
    }

    private bool $isHydrating = false;

    public string $password {
        get => $this->password ?? '';
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

    public ?DateTime $createdAt = null {
        get => $this->createdAt;
        set => $this->createdAt = $value;
    }

    public ?DateTime $updatedAt = null {
        get => $this->updatedAt;
        set => $this->updatedAt = $value;
    }

    public static function getTableName(): string
    {
        return 'user';
    }

    /**
     * Create a new User instance
     */
    public static function create(string $name, string $email, string $password): self
    {
        $user = new self();
        $user->name = $name;
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
        $this->email = (string) $this->bean->email;
        $this->password = (string) $this->bean->password; // Set raw hash during hydration

        if (!empty($this->bean->created_at)) {
            $this->createdAt = new DateTime($this->bean->created_at);
        }

        if (!empty($this->bean->updated_at)) {
            $this->updatedAt = new DateTime($this->bean->updated_at);
        }

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

        $this->bean->name = $this->name;
        $this->bean->email = $this->email;
        $this->bean->password = $this->password;

        // Set timestamps
        if ($this->createdAt !== null) {
            $this->bean->created_at = $this->createdAt->format('Y-m-d H:i:s');
        }

        // Always update the updated_at timestamp
        $this->updatedAt = new DateTime();
        $this->bean->updated_at = $this->updatedAt->format('Y-m-d H:i:s');

        // Set created_at if this is a new record
        if (!$this->exists() && $this->createdAt === null) {
            $this->createdAt = new DateTime();
            // @phpstan-ignore-next-line method.nonObject (property hooks guarantee non-null)
            $this->bean->created_at = $this->createdAt->format('Y-m-d H:i:s');
        }
    }

    /**
     * Convert user to array (for JSON responses)
     * @return array<string, mixed>
     */
    public function toArray(bool $includePassword = false): array
    {
        $data = [
            'id' => $this->getId(),
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];

        if ($includePassword) {
            $data['password'] = $this->password;
        }

        return $data;
    }
}
