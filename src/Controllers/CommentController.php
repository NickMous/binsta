<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Entities\Comment;
use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Repositories\CommentRepository;
use NickMous\Binsta\Requests\Comments\CreateCommentRequest;

class CommentController extends BaseController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
    ) {
    }

    /**
     * Store a new comment
     */
    public function store(CreateCommentRequest $request): JsonResponse
    {
        // Check if user is authenticated
        if (!isset($_SESSION['user'])) {
            return new JsonResponse([
                'message' => 'Authentication required to comment'
            ], 401);
        }

        $request->validate(true);

        try {
            // Create the comment using the repository
            $comment = $this->commentRepository->create(
                content: $request->get('content'),
                postId: $request->get('post_id'),
                userId: $request->get('user_id')
            );

            return new JsonResponse([
                'message' => 'Comment created successfully',
                'comment' => $comment->toArray()
            ], 201);
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('Comment creation failed: ' . $e->getMessage());

            return new JsonResponse([
                'message' => 'Failed to create comment',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get comments for a specific post
     */
    public function byPost(int $postId): JsonResponse
    {
        try {
            $comments = $this->commentRepository->findByPostIdWithUser($postId);

            return new JsonResponse([
                'comments' => $comments,
                'count' => count($comments)
            ]);
        } catch (\Exception $e) {
            error_log('Failed to fetch comments: ' . $e->getMessage());

            return new JsonResponse([
                'message' => 'Failed to fetch comments',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Delete a comment
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Check if user is authenticated
        if (!isset($_SESSION['user'])) {
            return new JsonResponse([
                'message' => 'Authentication required'
            ], 401);
        }

        // Check if user owns the comment
        if ($comment->userId !== $_SESSION['user']) {
            return new JsonResponse([
                'message' => 'You can only delete your own comments'
            ], 403);
        }

        try {
            $deleted = $this->commentRepository->deleteById($comment->getId());

            if (!$deleted) {
                return new JsonResponse([
                    'message' => 'Comment not found'
                ], 404);
            }

            return new JsonResponse([
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            error_log('Comment deletion failed: ' . $e->getMessage());

            return new JsonResponse([
                'message' => 'Failed to delete comment',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
