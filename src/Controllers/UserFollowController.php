<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Entities\UserFollow;
use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Repositories\UserFollowRepository;

class UserFollowController extends BaseController
{
    public function __construct(
        private readonly UserFollowRepository $repository,
    ) {
    }

    public function followStatus(int $userId): JsonResponse
    {
        $isFollowing = $this->repository->isFollowing($_SESSION['user'], $userId);

        return new JsonResponse(['isFollowing' => $isFollowing]);
    }

    /**
     * Follow a user
     */
    public function follow(int $userId): JsonResponse
    {
        $followLink = new UserFollow();
        $followLink->followerId = $_SESSION['user'];
        $followLink->followingId = $userId;
        $followLink->save();

        return new JsonResponse(['message' => "Followed user with ID: $userId"]);
    }

    /**
     * Unfollow a user
     */
    public function unfollow(int $userId): JsonResponse
    {
        $followLink = $this->repository->unfollow($_SESSION['user'], $userId);

        if (!$followLink) {
            return new JsonResponse(['message' => "You are not following user with ID: $userId"], 404);
        }

        return new JsonResponse(['message' => "Unfollowed user with ID: $userId"]);
    }
}
