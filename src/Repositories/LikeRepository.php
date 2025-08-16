<?php

namespace NickMous\Binsta\Repositories;

use NickMous\Binsta\Entities\Like;
use NickMous\Binsta\Internals\Exceptions\EntityNotFoundException;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Repositories\BaseRepository;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class LikeRepository extends BaseRepository
{
    public function findById(int $id): ?Like
    {
        $bean = R::load(Like::getTableName(), $id);

        if ($bean->id == 0) {
            return null;
        }

        return new Like($bean);
    }

    /**
     * Find a like by user ID and post ID
     */
    public function findByUserAndPost(int $userId, int $postId): ?Like
    {
        $bean = R::findOne(Like::getTableName(), 'user_id = ? AND post_id = ?', [$userId, $postId]);

        if ($bean === null) {
            return null;
        }

        return new Like($bean);
    }

    /**
     * Check if a user has liked a specific post
     */
    public function hasUserLikedPost(int $userId, int $postId): bool
    {
        return $this->findByUserAndPost($userId, $postId) !== null;
    }

    /**
     * Get the number of likes for a specific post
     */
    public function getLikeCountForPost(int $postId): int
    {
        return R::count(Like::getTableName(), 'post_id = ?', [$postId]);
    }

    /**
     * Get like counts for multiple posts
     * @param array<int> $postIds
     * @return array<int, int> Array mapping post ID to like count
     */
    public function getLikeCountsForPosts(array $postIds): array
    {
        if (empty($postIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($postIds) - 1) . '?';
        $results = R::getAll(
            "SELECT post_id, COUNT(*) as like_count 
             FROM `" . Like::getTableName() . "` 
             WHERE post_id IN ({$placeholders}) 
             GROUP BY post_id",
            $postIds
        );

        $counts = [];
        foreach ($results as $result) {
            $counts[(int)$result['post_id']] = (int)$result['like_count'];
        }

        // Ensure all post IDs have a count (0 if no likes)
        foreach ($postIds as $postId) {
            if (!isset($counts[$postId])) {
                $counts[$postId] = 0;
            }
        }

        return $counts;
    }

    /**
     * Get which posts a user has liked from a list of post IDs
     * @param array<int> $postIds
     * @return array<int> Array of post IDs that the user has liked
     */
    public function getUserLikedPosts(int $userId, array $postIds): array
    {
        if (empty($postIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($postIds) - 1) . '?';
        $results = R::getCol(
            "SELECT post_id 
             FROM `" . Like::getTableName() . "` 
             WHERE user_id = ? AND post_id IN ({$placeholders})",
            array_merge([$userId], $postIds)
        );

        return array_map('intval', $results);
    }

    /**
     * @return array<Like>
     */
    public function findByUserId(int $userId): array
    {
        $beans = R::find(Like::getTableName(), 'user_id = ? ORDER BY created_at DESC', [$userId]);

        return array_values(array_map(fn(OODBBean $bean) => new Like($bean), $beans));
    }

    /**
     * @return array<Like>
     */
    public function findByPostId(int $postId): array
    {
        $beans = R::find(Like::getTableName(), 'post_id = ? ORDER BY created_at DESC', [$postId]);

        return array_values(array_map(fn(OODBBean $bean) => new Like($bean), $beans));
    }

    public function create(int $userId, int $postId): Like
    {
        // Check if like already exists
        $existingLike = $this->findByUserAndPost($userId, $postId);
        if ($existingLike !== null) {
            return $existingLike;
        }

        $like = Like::create($userId, $postId);
        $like->save();

        return $like;
    }

    public function deleteByUserAndPost(int $userId, int $postId): bool
    {
        $like = $this->findByUserAndPost($userId, $postId);

        if ($like === null) {
            return false;
        }

        $like->delete();
        return true;
    }

    public function save(Like $like): Like
    {
        $like->save();
        return $like;
    }

    public function getEntityByParameter(string $parameterValue): Entity
    {
        if (is_numeric($parameterValue)) {
            $like = $this->findById((int)$parameterValue);
            if ($like !== null) {
                return $like;
            }
        }

        throw new EntityNotFoundException('Like', $parameterValue);
    }
}
