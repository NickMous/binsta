<?php

namespace NickMous\Binsta\Entities;

use DateTime;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Traits\HasTimestamps;
use NickMous\Binsta\Repositories\LikeRepository;

class Like extends Entity
{
    use HasTimestamps;

    public int $userId = 0;
    public int $postId = 0;

    public static function getTableName(): string
    {
        return 'like';
    }

    public static function create(int $userId, int $postId): self
    {
        $like = new self();
        $like->userId = $userId;
        $like->postId = $postId;
        $like->createdAt = new DateTime();

        return $like;
    }

    protected function hydrate(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->userId = (int) $this->bean->user_id;
        $this->postId = (int) $this->bean->post_id;

        $this->hydrateTimestamps();
    }

    protected function prepare(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->bean->user_id = $this->userId ?? 0;
        $this->bean->post_id = $this->postId ?? 0;

        $this->prepareTimestamps();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge([
            'id' => $this->getId(),
            'user_id' => $this->userId,
            'post_id' => $this->postId,
        ], $this->getTimestampArray());
    }

    public function getRepository(): string
    {
        return LikeRepository::class;
    }
}
