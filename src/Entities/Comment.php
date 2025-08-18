<?php

namespace NickMous\Binsta\Entities;

use DateTime;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Traits\HasTimestamps;
use NickMous\Binsta\Repositories\CommentRepository;

class Comment extends Entity
{
    use HasTimestamps;

    public string $content = '';
    public int $postId = 0;
    public int $userId = 0;

    public static function getTableName(): string
    {
        return 'comment';
    }

    public static function create(string $content, int $postId, int $userId): self
    {
        $comment = new self();
        $comment->content = $content;
        $comment->postId = $postId;
        $comment->userId = $userId;
        $comment->createdAt = new DateTime();

        return $comment;
    }

    protected function hydrate(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->content = (string) $this->bean->content;
        $this->postId = (int) $this->bean->post_id;
        $this->userId = (int) $this->bean->user_id;

        $this->hydrateTimestamps();
    }

    protected function prepare(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->bean->content = $this->content ?? '';
        $this->bean->post_id = $this->postId ?? 0;
        $this->bean->user_id = $this->userId ?? 0;

        $this->prepareTimestamps();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge([
            'id' => $this->getId(),
            'content' => $this->content,
            'post_id' => $this->postId,
            'user_id' => $this->userId,
        ], $this->getTimestampArray());
    }

    public function getRepository(): string
    {
        return CommentRepository::class;
    }
}
