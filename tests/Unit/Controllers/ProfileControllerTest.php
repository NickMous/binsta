<?php

use NickMous\Binsta\Controllers\ProfileController;
use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Requests\Profile\UpdateProfileRequest;
use NickMous\Binsta\Requests\Profile\ChangePasswordRequest;
use NickMous\Binsta\Requests\Profile\UploadProfilePictureRequest;

covers(ProfileController::class);

describe('ProfileController', function (): void {
    beforeEach(function (): void {
        // Clear session
        $_SESSION = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
    });

    afterEach(function (): void {
        $_SESSION = [];
        $_FILES = [];
        unset($_SERVER['REQUEST_METHOD']);
    });

    test('show method exists and handles unauthenticated user', function (): void {
        $controller = new ProfileController();

        expect(fn() => $controller->show())
            ->toThrow(\NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException::class);
    });

    test('update method exists and handles unauthenticated user', function (): void {
        $controller = new ProfileController();
        $request = new UpdateProfileRequest();

        expect(fn() => $controller->update($request))
            ->toThrow(\Exception::class);
    });

    test('changePassword method exists and handles unauthenticated user', function (): void {
        $controller = new ProfileController();
        $request = new ChangePasswordRequest();

        expect(fn() => $controller->changePassword($request))
            ->toThrow(\Exception::class);
    });

    test('uploadProfilePicture method exists and handles unauthenticated user', function (): void {
        $controller = new ProfileController();
        $request = new UploadProfilePictureRequest();

        expect(fn() => $controller->uploadProfilePicture($request))
            ->toThrow(\Exception::class);
    });
});
