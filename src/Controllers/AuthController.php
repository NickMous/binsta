<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Repositories\UserRepository;
use NickMous\Binsta\Requests\Auth\LoginRequest;
use NickMous\Binsta\Requests\Auth\RegisterRequest;

class AuthController extends BaseController
{
    public function login(LoginRequest $request): Response
    {
        $request->validate(true);

        $user = UserRepository::authenticate(
            $request->get('email'),
            $request->get('password')
        );

        if ($user === null) {
            throw new ValidationFailedException(
                ['email' => ['Invalid email or password']],
                true
            );
        }

        return new JsonResponse([
            'message' => 'Login successful',
            'user' => $user->toArray()
        ]);
    }

    public function register(RegisterRequest $request): Response
    {
        $request->validate(true);

        $user = UserRepository::create(
            $request->get('name'),
            $request->get('email'),
            $request->get('password')
        );

        return new JsonResponse([
            'message' => 'User registered successfully',
            'user' => $user->toArray()
        ], 201);
    }
}
