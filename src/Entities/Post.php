<?php

namespace NickMous\Binsta\Entities;

use DateTime;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Traits\HasTimestamps;
use NickMous\Binsta\Repositories\PostRepository;

class Post extends Entity
{
    use HasTimestamps;

    public string $title = '';
    public string $description = '';
    public string $code = '';
    public string $programmingLanguage = '';
    public string $codeTheme = 'github-dark';
    public int $userId = 0;
    public ?int $originalPostId = null;

    public static function getTableName(): string
    {
        return 'post';
    }

    public static function create(string $title, string $description, string $code, string $programmingLanguage, int $userId, ?int $originalPostId = null, string $codeTheme = 'github-dark'): self
    {
        $post = new self();
        $post->title = $title;
        $post->description = $description;
        $post->code = $code;
        $post->programmingLanguage = $programmingLanguage;
        $post->codeTheme = $codeTheme;
        $post->userId = $userId;
        $post->originalPostId = $originalPostId;
        $post->createdAt = new DateTime();

        return $post;
    }

    protected function hydrate(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->title = (string) $this->bean->title;
        $this->description = (string) $this->bean->description;
        $this->code = (string) $this->bean->code;
        $this->programmingLanguage = (string) $this->bean->programming_language;
        $this->codeTheme = (string) ($this->bean->code_theme ?: 'github-dark');
        $this->userId = (int) $this->bean->user_id;
        $this->originalPostId = $this->bean->original_post_id ? (int) $this->bean->original_post_id : null;

        $this->hydrateTimestamps();
    }

    protected function prepare(): void
    {
        if ($this->bean === null) {
            return;
        }

        $this->bean->title = $this->title ?? '';
        $this->bean->description = $this->description ?? '';
        $this->bean->code = $this->code ?? '';
        $this->bean->programming_language = $this->programmingLanguage ?? '';
        $this->bean->code_theme = $this->codeTheme ?? 'github-dark';
        $this->bean->user_id = $this->userId ?? 0;
        $this->bean->original_post_id = $this->originalPostId;

        $this->prepareTimestamps();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge([
            'id' => $this->getId(),
            'title' => $this->title,
            'description' => $this->description,
            'code' => $this->code,
            'programming_language' => $this->programmingLanguage,
            'code_theme' => $this->codeTheme,
            'user_id' => $this->userId,
            'original_post_id' => $this->originalPostId,
        ], $this->getTimestampArray());
    }

    public function getRepository(): string
    {
        return PostRepository::class;
    }
}
