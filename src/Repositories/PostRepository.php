<?php

namespace NickMous\Binsta\Repositories;

use NickMous\Binsta\Entities\Post;
use NickMous\Binsta\Internals\Exceptions\EntityNotFoundException;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Repositories\BaseRepository;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class PostRepository extends BaseRepository
{
    public function findById(int $id): ?Post
    {
        $bean = R::load(Post::getTableName(), $id);

        if ($bean->id == 0) {
            return null;
        }

        return new Post($bean);
    }

    /**
     * @return array<Post>
     */
    public function findByUserId(int $userId): array
    {
        $beans = R::find(Post::getTableName(), 'user_id = ? ORDER BY created_at DESC', [$userId]);

        return array_values(array_map(fn(OODBBean $bean) => new Post($bean), $beans));
    }

    /**
     * @return array<Post>
     */
    public function findByProgrammingLanguage(string $language): array
    {
        $beans = R::find(Post::getTableName(), 'programming_language = ? ORDER BY created_at DESC', [$language]);

        return array_values(array_map(fn(OODBBean $bean) => new Post($bean), $beans));
    }

    /**
     * @return array<Post>
     */
    public function searchByTitle(string $query): array
    {
        $beans = R::find(Post::getTableName(), 'title LIKE ? ORDER BY created_at DESC', ['%' . $query . '%']);

        return array_values(array_map(fn(OODBBean $bean) => new Post($bean), $beans));
    }

    public function create(string $title, string $description, string $code, string $programmingLanguage, int $userId): Post
    {
        $post = Post::create($title, $description, $code, $programmingLanguage, $userId);
        $post->save();

        return $post;
    }

    /**
     * @return array<Post>
     */
    public function findAll(): array
    {
        $beans = R::find(Post::getTableName(), 'ORDER BY created_at DESC');

        return array_values(array_map(fn(OODBBean $bean) => new Post($bean), $beans));
    }

    /**
     * @return array<Post>
     */
    public function findRecent(int $limit = 10): array
    {
        $beans = R::find(Post::getTableName(), 'ORDER BY created_at DESC LIMIT ?', [$limit]);

        return array_values(array_map(fn(OODBBean $bean) => new Post($bean), $beans));
    }

    public function count(): int
    {
        return R::count(Post::getTableName());
    }

    public function countByUserId(int $userId): int
    {
        return R::count(Post::getTableName(), 'user_id = ?', [$userId]);
    }

    public function countByProgrammingLanguage(string $language): int
    {
        return R::count(Post::getTableName(), 'programming_language = ?', [$language]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): ?Post
    {
        $post = $this->findById($id);

        if ($post === null) {
            return null;
        }

        if (isset($data['title'])) {
            $post->title = $data['title'];
        }

        if (isset($data['description'])) {
            $post->description = $data['description'];
        }

        if (isset($data['code'])) {
            $post->code = $data['code'];
        }

        if (isset($data['programming_language'])) {
            $post->programmingLanguage = $data['programming_language'];
        }

        $post->save();

        return $post;
    }

    public function deleteById(int $id): bool
    {
        $post = $this->findById($id);

        if ($post === null) {
            return false;
        }

        $post->delete();
        return true;
    }

    public function save(Post $post): Post
    {
        $post->save();
        return $post;
    }

    public function getEntityByParameter(string $parameterValue): Entity
    {
        if (is_numeric($parameterValue)) {
            $post = $this->findById((int)$parameterValue);
            if ($post !== null) {
                return $post;
            }
        }

        throw new EntityNotFoundException('Post', $parameterValue);
    }

    /**
     * @return array<string>
     */
    public function getProgrammingLanguages(): array
    {
        $result = R::getCol('SELECT DISTINCT programming_language FROM ' . Post::getTableName() . ' ORDER BY programming_language');

        return array_filter($result, fn($lang) => !empty($lang));
    }

    /**
     * @return array<Post>
     */
    public function findCreatedAfter(\DateTime $date): array
    {
        $beans = R::find(
            Post::getTableName(),
            'created_at > ? ORDER BY created_at DESC',
            [$date->format('Y-m-d H:i:s')]
        );

        return array_values(array_map(fn(OODBBean $bean) => new Post($bean), $beans));
    }

    /**
     * @return array<Post>
     */
    public function searchPosts(string $query): array
    {
        $query = '%' . $query . '%';
        $beans = R::find(
            Post::getTableName(),
            'title LIKE ? OR description LIKE ? OR programming_language LIKE ? ORDER BY created_at DESC LIMIT 20',
            [$query, $query, $query]
        );

        return array_values(array_map(fn(OODBBean $bean) => new Post($bean), $beans));
    }

    /**
     * Get posts from users that the given user follows
     * @return array<Post>
     */
    public function findFromFollowedUsers(int $userId, int $limit = 20): array
    {
        $postTable = Post::getTableName();
        $followTable = 'userfollow';
        
        $beans = R::getAll(
            "SELECT p.* FROM {$postTable} p 
             INNER JOIN {$followTable} uf ON p.user_id = uf.following_id 
             WHERE uf.follower_id = ? 
             ORDER BY p.created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        );

        return array_values(array_map(
            fn(array $beanData) => new Post(R::convertToBean(Post::getTableName(), $beanData)),
            $beans
        ));
    }
}
