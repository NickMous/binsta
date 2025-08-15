<?php

namespace NickMous\Binsta\Entities;

use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Traits\HasCreatedAt;
use NickMous\Binsta\Repositories\UserFollowRepository;

class UserFollow extends Entity
{
    use HasCreatedAt;

    public int $followerId;
    public int $followingId;

    public static function getTableName(): string
    {
        return 'userfollow';
    }

    /**
     * Create a new UserFollow relationship
     */
    public static function create(int $followerId, int $followingId): self
    {
        $follow = new self();
        $follow->followerId = $followerId;
        $follow->followingId = $followingId;

        return $follow;
    }

    /**
     * Hydrate entity properties from the bean
     */
    protected function hydrate(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->followerId = (int) $this->bean->follower_id;
        $this->followingId = (int) $this->bean->following_id;

        $this->hydrateCreatedAt();
    }

    /**
     * Prepare the bean before saving
     */
    protected function prepare(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->bean->follower_id = $this->followerId;
        $this->bean->following_id = $this->followingId;

        $this->prepareCreatedAt();
    }

    /**
     * Convert follow to array (for JSON responses)
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge([
            'id' => $this->getId(),
            'follower_id' => $this->followerId,
            'following_id' => $this->followingId,
        ], $this->getCreatedAtArray());
    }

    public function getRepository(): string
    {
        return UserFollowRepository::class;
    }
}
