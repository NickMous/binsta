<?php

use NickMous\Binsta\Requests\Profile\UploadProfilePictureRequest;

covers(UploadProfilePictureRequest::class);

describe('UploadProfilePictureRequest', function (): void {
    beforeEach(function (): void {
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
    });

    afterEach(function (): void {
        $_FILES = [];
        unset($_SERVER['REQUEST_METHOD']);
    });

    test('defines correct validation rules', function (): void {
        $request = new UploadProfilePictureRequest();
        $rules = $request->rules();

        expect($rules)->toHaveKey('profile_picture');

        expect($rules['profile_picture'])->toContain('required');
        expect($rules['profile_picture'])->toContain('file');
        expect($rules['profile_picture'])->toContain('image');
        expect($rules['profile_picture'])->toContain('max:2048'); // 2MB in KB
    });

    test('defines correct validation messages', function (): void {
        $request = new UploadProfilePictureRequest();
        $messages = $request->messages();

        expect($messages)->toHaveKey('profile_picture.required')
            ->and($messages)->toHaveKey('profile_picture.file')
            ->and($messages)->toHaveKey('profile_picture.image')
            ->and($messages)->toHaveKey('profile_picture.max')
            ->and($messages['profile_picture.required'])->toBe('Profile picture is required.')
            ->and($messages['profile_picture.file'])->toBe('Profile picture must be a file.')
            ->and($messages['profile_picture.image'])->toBe('Profile picture must be an image.')
            ->and($messages['profile_picture.max'])->toBe('Profile picture must be less than 2MB.');
    });

    test('validates successfully with valid image file', function (): void {
        // Create temporary image file with proper PNG header
        $tempFile = tempnam(sys_get_temp_dir(), 'test_image');
        // Proper 1x1 PNG with complete headers that mime_content_type can detect
        $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQIHWNgAAIAAAUAAY27m/MAAAAASUVORK5CYII=');
        file_put_contents($tempFile, $imageData);

        // Mock $_FILES global
        $_FILES['profile_picture'] = [
            'name' => 'test.png',
            'type' => 'image/png',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => strlen($imageData)
        ];

        $request = new UploadProfilePictureRequest();

        // Should not throw exception
        $request->validate(true);
        expect(true)->toBe(true);

        // Clean up
        unlink($tempFile);
    });

    test('fails validation when no file is uploaded', function (): void {
        // Mock no file upload
        $_FILES['profile_picture'] = [
            'name' => '',
            'type' => '',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 0
        ];

        $request = new UploadProfilePictureRequest();

        expect(fn() => $request->validate(true))
            ->toThrow(\NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException::class);
    });

    test('fails validation when file is too large', function (): void {
        // Create temporary large file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_large');
        $largeData = str_repeat('x', 3 * 1024 * 1024); // 3MB
        file_put_contents($tempFile, $largeData);

        // Mock large file upload
        $_FILES['profile_picture'] = [
            'name' => 'large.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => strlen($largeData)
        ];

        $request = new UploadProfilePictureRequest();

        expect(fn() => $request->validate(true))
            ->toThrow(\NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException::class);

        // Clean up
        unlink($tempFile);
    });
});
