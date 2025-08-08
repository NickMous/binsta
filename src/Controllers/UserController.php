<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\JsonResponse;

class UserController extends BaseController
{
    /**
     * Show user - the User entity is automatically resolved from the {user} route parameter
     */
    public function show(User $user): JsonResponse
    {
        return new JsonResponse($user->toArray());
    }
}
