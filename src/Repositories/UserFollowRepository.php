<?php

namespace NickMous\Binsta\Repositories;

use NickMous\Binsta\Entities\UserFollow;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Repositories\BaseRepository;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class UserFollowRepository extends BaseRepository
{
    /**
     * Find a user follow relationship by ID
     */
    public function findById(int $id): ?UserFollow
    {
        $bean = R::load(UserFollow::getTableName(), $id);

        if ($bean->id == 0) {
            return null;
        }

        return new UserFollow($bean);
    }

    /**
     * Find follow relationship between two users
     */
    public function findByUsers(int $followerId, int $followingId): ?UserFollow
    {
        $bean = R::findOne(
            UserFollow::getTableName(),
            'follower_id = ? AND following_id = ?',
            [$followerId, $followingId]
        );

        if ($bean === null) {
            return null;
        }

        return new UserFollow($bean);
    }

    /**
     * Check if user is following another user
     */
    public function isFollowing(int $followerId, int $followingId): bool
    {
        return $this->findByUsers($followerId, $followingId) !== null;
    }

    /**
     * Create a new follow relationship
     */
    public function create(int $followerId, int $followingId): UserFollow
    {
        $follow = UserFollow::create($followerId, $followingId);
        $follow->save();

        return $follow;
    }

    /**
     * Remove a follow relationship
     */
    public function unfollow(int $followerId, int $followingId): bool
    {
        $follow = $this->findByUsers($followerId, $followingId);

        if ($follow === null) {
            return false;
        }

        $follow->delete();
        return true;
    }

    /**
     * Get all users that a user is following
     * @return array<UserFollow>
     */
    public function getFollowing(int $userId): array
    {
        $beans = R::find(
            UserFollow::getTableName(),
            'follower_id = ? ORDER BY created_at DESC',
            [$userId]
        );

        return array_values(array_map(fn(OODBBean $bean) => new UserFollow($bean), $beans));
    }

    /**
     * Get all followers of a user
     * @return array<UserFollow>
     */
    public function getFollowers(int $userId): array
    {
        $beans = R::find(
            UserFollow::getTableName(),
            'following_id = ? ORDER BY created_at DESC',
            [$userId]
        );

        return array_values(array_map(fn(OODBBean $bean) => new UserFollow($bean), $beans));
    }

    /**
     * Count how many users a user is following
     */
    public function getFollowingCount(int $userId): int
    {
        return R::count(UserFollow::getTableName(), 'follower_id = ?', [$userId]);
    }

    /**
     * Count how many followers a user has
     */
    public function getFollowersCount(int $userId): int
    {
        return R::count(UserFollow::getTableName(), 'following_id = ?', [$userId]);
    }

    /**
     * Get mutual follows (users who follow each other)
     * @return array<UserFollow>
     */
    public function getMutualFollows(int $userId): array
    {
        $tableName = UserFollow::getTableName();
        $beans = R::getAll(
            "SELECT uf1.* FROM {$tableName} uf1 
             INNER JOIN {$tableName} uf2 ON uf1.follower_id = uf2.following_id 
             AND uf1.following_id = uf2.follower_id 
             WHERE uf1.follower_id = ?
             ORDER BY uf1.created_at DESC",
            [$userId]
        );

        return array_values(array_map(
            fn(array $beanData) => new UserFollow(R::convertToBean(UserFollow::getTableName(), $beanData)),
            $beans
        ));
    }

    /**
     * Save a UserFollow entity
     */
    public function save(UserFollow $userFollow): UserFollow
    {
        $userFollow->save();
        return $userFollow;
    }

    public function getEntityByParameter(string $parameterValue): Entity
    {
        if (is_numeric($parameterValue)) {
            $follow = $this->findById((int)$parameterValue);
            if ($follow !== null) {
                return $follow;
            }
        }

        throw new \InvalidArgumentException("UserFollow not found for parameter: $parameterValue");
    }
}
