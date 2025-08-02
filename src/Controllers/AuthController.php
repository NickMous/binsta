<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Requests\Auth\LoginRequest;
use NickMous\Binsta\Requests\Auth\RegisterRequest;

class AuthController extends BaseController
{
    public function login(LoginRequest $request): Response
    {
        $request->validate(true);
        return new JsonResponse([]);
    }

    public function register(RegisterRequest $request): Response
    {
        $request->validate(true);

        return new JsonResponse([
            'message' => 'User registered successfully',
            'user' => [
                'name' => $request->get('name'),
                'email' => $request->get('email'),
            ]
        ]);
    }
}
