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
    private const string UPLOAD_DIR = 'uploads/profiles/';

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
                    ['username' => 'Username is already taken'],
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
                    ['email' => 'Email is already taken'],
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
                ['current_password' => 'Current password is incorrect'],
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
                ['profile_picture' => 'File upload failed'],
                true
            );
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($uploadedFile['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            throw new ValidationFailedException(
                ['profile_picture' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed'],
                true
            );
        }

        // Validate file size (2MB max)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($uploadedFile['size'] > $maxSize) {
            throw new ValidationFailedException(
                ['profile_picture' => 'File size must be less than 2MB'],
                true
            );
        }

        // Generate unique filename with .webp extension
        $filename = 'profile_' . $user->getId() . '_' . time() . '.webp';

        // Create uploads directory if it doesn't exist
        if (!is_dir(self::UPLOAD_DIR) && !mkdir(self::UPLOAD_DIR, 0755, true) && !is_dir(self::UPLOAD_DIR)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', self::UPLOAD_DIR));
        }

        $uploadPath = self::UPLOAD_DIR . $filename;

        // Process and save the image
        if (!$this->processAndSaveImage($uploadedFile['tmp_name'], $uploadPath, $fileType)) {
            throw new ValidationFailedException(
                ['profile_picture' => 'Failed to process and save image'],
                true
            );
        }

        // Delete old profile picture if exists
        if ($user->profilePicture) {
            $oldFilePath = self::UPLOAD_DIR . basename($user->profilePicture);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        // Update user profile picture
        $user->profilePicture = '/uploads/profiles/' . $filename;
        $this->userRepository->save($user);

        return new JsonResponse([
            'message' => 'Profile picture uploaded successfully',
            'user' => $user->toArray()
        ]);
    }

    private function processAndSaveImage(string $sourcePath, string $destinationPath, string $mimeType): bool
    {
        // Define target dimensions for profile pictures
        $targetWidth = 400;
        $targetHeight = 400;
        $quality = 80; // WebP quality

        // Create image resource from source
        $sourceImage = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => false
        };

        if (!$sourceImage) {
            return false;
        }

        // Get original dimensions
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);

        // Calculate crop dimensions to make square
        $cropSize = min($originalWidth, $originalHeight);
        $cropX = (int) (($originalWidth - $cropSize) / 2);
        $cropY = (int) (($originalHeight - $cropSize) / 2);

        // Create square image
        $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Handle transparency for PNG, GIF, and WebP
        if (in_array($mimeType, ['image/png', 'image/gif', 'image/webp'])) {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefill($resizedImage, 0, 0, $transparent);
        }

        // Crop and resize to square
        imagecopyresampled(
            $resizedImage,
            $sourceImage,
            0,
            0,
            $cropX,
            $cropY,
            $targetWidth,
            $targetHeight,
            $cropSize,
            $cropSize
        );

        // Save as WebP
        $success = imagewebp($resizedImage, $destinationPath, $quality);

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return $success;
    }

    private function getCurrentUser(): User
    {
        $userId = $_SESSION['user'] ?? null;

        if (!$userId) {
            throw new ValidationFailedException(
                ['auth' => 'User not authenticated'],
                true
            );
        }

        $user = $this->userRepository->findById((int) $userId);

        if (!$user) {
            throw new ValidationFailedException(
                ['auth' => 'User not found'],
                true
            );
        }

        return $user;
    }
}
