<?php

namespace NickMous\Binsta\Repositories;

use NickMous\Binsta\Entities\Comment;
use NickMous\Binsta\Internals\Exceptions\EntityNotFoundException;
use NickMous\Binsta\Internals\Entities\Entity;
use NickMous\Binsta\Internals\Repositories\BaseRepository;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class CommentRepository extends BaseRepository
{
    public function findById(int $id): ?Comment
    {
        $bean = R::load(Comment::getTableName(), $id);

        if ($bean->id == 0) {
            return null;
        }

        return new Comment($bean);
    }

    /**
     * Find comments by post ID with user information
     * @return array<array<string, mixed>>
     */
    public function findByPostIdWithUser(int $postId): array
    {
        $commentTable = Comment::getTableName();
        $userTable = 'user';

        $results = R::getAll(
            "SELECT c.*, u.name as user_name, u.username as user_username, u.profile_picture as user_profile_picture 
             FROM {$commentTable} c 
             INNER JOIN {$userTable} u ON c.user_id = u.id 
             WHERE c.post_id = ? 
             ORDER BY c.created_at DESC",
            [$postId]
        );

        return $results;
    }

    /**
     * @return array<Comment>
     */
    public function findByPostId(int $postId): array
    {
        $beans = R::find(Comment::getTableName(), 'post_id = ? ORDER BY created_at ASC', [$postId]);

        return array_values(array_map(fn(OODBBean $bean) => new Comment($bean), $beans));
    }

    /**
     * @return array<Comment>
     */
    public function findByUserId(int $userId): array
    {
        $beans = R::find(Comment::getTableName(), 'user_id = ? ORDER BY created_at DESC', [$userId]);

        return array_values(array_map(fn(OODBBean $bean) => new Comment($bean), $beans));
    }

    public function create(string $content, int $postId, int $userId): Comment
    {
        $comment = Comment::create($content, $postId, $userId);
        $comment->save();

        return $comment;
    }

    /**
     * @return array<Comment>
     */
    public function findAll(): array
    {
        $beans = R::find(Comment::getTableName(), 'ORDER BY created_at DESC');

        return array_values(array_map(fn(OODBBean $bean) => new Comment($bean), $beans));
    }

    public function count(): int
    {
        return R::count(Comment::getTableName());
    }

    public function countByPostId(int $postId): int
    {
        return R::count(Comment::getTableName(), 'post_id = ?', [$postId]);
    }

    public function countByUserId(int $userId): int
    {
        return R::count(Comment::getTableName(), 'user_id = ?', [$userId]);
    }

    public function deleteById(int $id): bool
    {
        $comment = $this->findById($id);

        if ($comment === null) {
            return false;
        }

        $comment->delete();
        return true;
    }

    public function save(Comment $comment): Comment
    {
        $comment->save();
        return $comment;
    }

    public function getEntityByParameter(string $parameterValue): Entity
    {
        if (is_numeric($parameterValue)) {
            $comment = $this->findById((int)$parameterValue);
            if ($comment !== null) {
                return $comment;
            }
        }

        throw new EntityNotFoundException('Comment', $parameterValue);
    }
}
