<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Repositories\UserFollowRepository;

class UserController extends BaseController
{
    public function __construct(
        private readonly UserFollowRepository $userFollowRepository,
    ) {
    }

    /**
     * Show user - the User entity is automatically resolved from the {user} route parameter
     */
    public function show(User $user): JsonResponse
    {
        return new JsonResponse($user->toArray());
    }

    /**
     * Get user statistics (followers and following counts)
     */
    public function statistics(User $user): JsonResponse
    {
        $followersCount = $this->userFollowRepository->getFollowersCount($user->getId());
        $followingCount = $this->userFollowRepository->getFollowingCount($user->getId());

        return new JsonResponse([
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
        ]);
    }
}
