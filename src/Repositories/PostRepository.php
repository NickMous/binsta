<?php

namespace NickMous\Binsta\Repositories;

use NickMous\Binsta\Entities\Post;
use NickMous\Binsta\Internals\Exceptions\EntityNotFoundException;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Repositories\BaseRepository;
use NickMous\Binsta\Repositories\LikeRepository;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class PostRepository extends BaseRepository
{
    public function __construct(
        private readonly LikeRepository $likeRepository,
    ) {
    }
    public function findById(int $id): ?Post
    {
        $bean = R::load(Post::getTableName(), $id);

        if ($bean->id == 0) {
            return null;
        }

        return new Post($bean);
    }

    /**
     * Find a post by ID with user information
     * @return array<string, mixed>|null
     */
    public function findByIdWithUser(int $id, ?int $currentUserId = null): ?array
    {
        $postTable = Post::getTableName();
        $userTable = 'user';

        $result = R::getRow(
            "SELECT p.*, u.name as user_name, u.username as user_username, u.profile_picture as user_profile_picture 
             FROM {$postTable} p 
             INNER JOIN {$userTable} u ON p.user_id = u.id 
             WHERE p.id = ?",
            [$id]
        );

        if ($result) {
            $result = $this->addLikeInformation([$result], $currentUserId)[0];
        }

        return $result ?: null;
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
     * Get posts by user ID with user information
     * @return array<array<string, mixed>>
     */
    public function findByUserIdWithUser(int $userId, int $limit = 20, ?int $currentUserId = null): array
    {
        $postTable = Post::getTableName();
        $userTable = 'user';

        $results = R::getAll(
            "SELECT p.*, u.name as user_name, u.username as user_username, u.profile_picture as user_profile_picture 
             FROM {$postTable} p 
             INNER JOIN {$userTable} u ON p.user_id = u.id 
             WHERE p.user_id = ? 
             ORDER BY p.created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        );

        return $this->addLikeInformation($results, $currentUserId);
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
     * @return array<array<string, mixed>>
     */
    public function findRecent(int $limit = 10, ?int $currentUserId = null): array
    {
        $postTable = Post::getTableName();
        $userTable = 'user';

        $results = R::getAll(
            "SELECT p.*, u.name as user_name, u.username as user_username, u.profile_picture as user_profile_picture 
             FROM {$postTable} p 
             INNER JOIN {$userTable} u ON p.user_id = u.id 
             ORDER BY p.created_at DESC 
             LIMIT ?",
            [$limit]
        );

        return $this->addLikeInformation($results, $currentUserId);
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
     * @return array<array<string, mixed>>
     */
    public function findFromFollowedUsers(int $userId, int $limit = 20): array
    {
        $postTable = Post::getTableName();
        $followTable = 'userfollow';
        $userTable = 'user';

        $results = R::getAll(
            "SELECT p.*, u.name as user_name, u.username as user_username, u.profile_picture as user_profile_picture 
             FROM {$postTable} p 
             INNER JOIN {$followTable} uf ON p.user_id = uf.following_id 
             INNER JOIN {$userTable} u ON p.user_id = u.id 
             WHERE uf.follower_id = ? 
             ORDER BY p.created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        );

        return $this->addLikeInformation($results, $userId);
    }

    /**
     * Add like information to an array of posts
     * @param array<array<string, mixed>> $posts
     * @return array<array<string, mixed>>
     */
    private function addLikeInformation(array $posts, ?int $currentUserId = null): array
    {
        if (empty($posts)) {
            return $posts;
        }

        // Extract post IDs
        $postIds = array_map(fn($post) => (int)$post['id'], $posts);

        // Get like counts for all posts
        $likeCounts = $this->likeRepository->getLikeCountsForPosts($postIds);

        // Get user liked posts if user is logged in
        $userLikedPosts = $currentUserId
            ? $this->likeRepository->getUserLikedPosts($currentUserId, $postIds)
            : [];

        // Add like information to each post
        foreach ($posts as &$post) {
            $postId = (int)$post['id'];
            $post['like_count'] = $likeCounts[$postId] ?? 0;
            $post['user_liked'] = in_array($postId, $userLikedPosts);
        }

        return $posts;
    }
}
