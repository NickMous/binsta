<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Repositories\UserRepository;
use NickMous\Binsta\Requests\Profile\UpdateProfileRequest;
use NickMous\Binsta\Requests\Profile\ChangePasswordRequest;
use NickMous\Binsta\Requests\Profile\UploadProfilePictureRequest;

class ProfileController extends BaseController
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }
    public function show(): Response
    {
        $user = $this->getCurrentUser();

        return new JsonResponse([
            'user' => $user->toArray()
        ]);
    }

    public function update(UpdateProfileRequest $request): Response
    {
        $request->validate(true);

        $user = $this->getCurrentUser();

        // Update all profile fields
        if ($request->has('name')) {
            $user->name = $request->get('name');
        }

        if ($request->has('username')) {
            // Check if username is already taken by another user
            $existingUser = $this->userRepository->findByUsername($request->get('username'));
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                throw new ValidationFailedException(
                    ['username' => ['Username is already taken']],
                    true
                );
            }
            $user->username = $request->get('username');
        }

        if ($request->has('email')) {
            // Check if email is already taken by another user
            $existingUser = $this->userRepository->findByEmail($request->get('email'));
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                throw new ValidationFailedException(
                    ['email' => ['Email is already taken']],
                    true
                );
            }
            $user->email = $request->get('email');
        }

        if ($request->has('biography')) {
            $user->biography = $request->get('biography');
        }

        $this->userRepository->save($user);

        return new JsonResponse([
            'message' => 'Profile updated successfully',
            'user' => $user->toArray()
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): Response
    {
        $request->validate(true);

        $user = $this->getCurrentUser();

        // Verify current password
        if (!$user->verifyPassword($request->get('current_password'))) {
            throw new ValidationFailedException(
                ['current_password' => ['Current password is incorrect']],
                true
            );
        }

        // Update password
        $user->password = $request->get('new_password');
        $this->userRepository->save($user);

        return new JsonResponse([
            'message' => 'Password changed successfully'
        ]);
    }

    public function uploadProfilePicture(UploadProfilePictureRequest $request): Response
    {
        $request->validate(true);

        $user = $this->getCurrentUser();

        // Handle file upload
        $uploadedFile = $_FILES['profile_picture'] ?? null;

        if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new ValidationFailedException(
                ['profile_picture' => ['File upload failed']],
                true
            );
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($uploadedFile['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            throw new ValidationFailedException(
                ['profile_picture' => ['Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']],
                true
            );
        }

        // Validate file size (2MB max)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($uploadedFile['size'] > $maxSize) {
            throw new ValidationFailedException(
                ['profile_picture' => ['File size must be less than 2MB']],
                true
            );
        }

        // Generate unique filename
        $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $user->getId() . '_' . time() . '.' . $extension;

        // Create uploads directory if it doesn't exist
        $uploadDir = 'public/uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadPath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
            throw new ValidationFailedException(
                ['profile_picture' => ['Failed to save uploaded file']],
                true
            );
        }

        // Delete old profile picture if exists
        if ($user->profilePicture && file_exists('public' . $user->profilePicture)) {
            unlink('public' . $user->profilePicture);
        }

        // Update user profile picture
        $user->profilePicture = '/uploads/profiles/' . $filename;
        $this->userRepository->save($user);

        return new JsonResponse([
            'message' => 'Profile picture uploaded successfully',
            'user' => $user->toArray()
        ]);
    }

    private function getCurrentUser(): User
    {
        $userId = $_SESSION['user'] ?? null;

        if (!$userId) {
            throw new ValidationFailedException(
                ['auth' => ['User not authenticated']],
                true
            );
        }

        $user = $this->userRepository->findById((int) $userId);

        if (!$user) {
            throw new ValidationFailedException(
                ['auth' => ['User not found']],
                true
            );
        }

        return $user;
    }
}
