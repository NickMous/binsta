<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Repositories\UserRepository;
use NickMous\Binsta\Requests\Auth\LoginRequest;
use NickMous\Binsta\Requests\Auth\RegisterRequest;

class AuthController extends BaseController
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function login(LoginRequest $request): Response
    {
        $request->validate(true);

        $user = $this->userRepository->authenticate(
            $request->get('email'),
            $request->get('password')
        );

        if ($user === null) {
            throw new ValidationFailedException(
                ['email' => 'Invalid email or password'],
                true
            );
        }

        $this->saveUserSession($user);

        return new JsonResponse([
            'message' => 'Login successful',
            'user' => $user->toArray()
        ]);
    }

    protected function saveUserSession(User $user): void
    {
        $_SESSION['user'] = $user->getId();
    }

    public function register(RegisterRequest $request): Response
    {
        $request->validate(true);

        $user = $this->userRepository->create(
            $request->get('name'),
            $request->get('username'),
            $request->get('email'),
            $request->get('password')
        );

        $this->saveUserSession($user);

        return new JsonResponse([
            'message' => 'User registered successfully',
            'user' => $user->toArray()
        ], 201);
    }

    public function logout(): Response
    {
        // Clear the user session
        unset($_SESSION['user']);

        // Optionally regenerate session ID for security
        session_regenerate_id(true);

        return new JsonResponse([
            'message' => 'Logged out successfully'
        ]);
    }
}
