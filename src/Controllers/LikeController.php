<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Entities\Post;
use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Repositories\LikeRepository;

class LikeController extends BaseController
{
    public function __construct(
        private readonly LikeRepository $likeRepository,
    ) {
    }

    /**
     * Like a post
     */
    public function like(Post $post): JsonResponse
    {
        $userId = $_SESSION['user'] ?? null;

        if (!$userId) {
            return new JsonResponse([
                'message' => 'Authentication required'
            ], 401);
        }

        // Check if already liked
        if ($this->likeRepository->hasUserLikedPost($userId, $post->getId())) {
            return new JsonResponse([
                'message' => 'Post already liked',
                'liked' => true,
                'like_count' => $this->likeRepository->getLikeCountForPost($post->getId())
            ]);
        }

        // Create the like
        $this->likeRepository->create($userId, $post->getId());

        return new JsonResponse([
            'message' => 'Post liked successfully',
            'liked' => true,
            'like_count' => $this->likeRepository->getLikeCountForPost($post->getId())
        ], 201);
    }

    /**
     * Unlike a post
     */
    public function unlike(Post $post): JsonResponse
    {
        $userId = $_SESSION['user'] ?? null;

        if (!$userId) {
            return new JsonResponse([
                'message' => 'Authentication required'
            ], 401);
        }

        // Check if not liked
        if (!$this->likeRepository->hasUserLikedPost($userId, $post->getId())) {
            return new JsonResponse([
                'message' => 'Post not liked',
                'liked' => false,
                'like_count' => $this->likeRepository->getLikeCountForPost($post->getId())
            ]);
        }

        // Remove the like
        $this->likeRepository->deleteByUserAndPost($userId, $post->getId());

        return new JsonResponse([
            'message' => 'Post unliked successfully',
            'liked' => false,
            'like_count' => $this->likeRepository->getLikeCountForPost($post->getId())
        ]);
    }

    /**
     * Get like status and count for a post
     */
    public function status(Post $post): JsonResponse
    {
        $userId = $_SESSION['user'] ?? null;

        $likeCount = $this->likeRepository->getLikeCountForPost($post->getId());
        $userLiked = $userId ? $this->likeRepository->hasUserLikedPost($userId, $post->getId()) : false;

        return new JsonResponse([
            'liked' => $userLiked,
            'like_count' => $likeCount
        ]);
    }
}
