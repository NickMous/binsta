<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Requests\LoginRequest;

class AuthController extends BaseController
{
    public function login(LoginRequest $request): Response
    {
        $request->validate(true);
        return new JsonResponse([]);
    }
}
