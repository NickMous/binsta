<?php

use NickMous\Binsta\Requests\Profile\ChangePasswordRequest;

covers(ChangePasswordRequest::class);

describe('ChangePasswordRequest', function (): void {
    beforeEach(function (): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
    });

    afterEach(function (): void {
        unset($_SERVER['REQUEST_METHOD']);
    });

    test('defines correct validation rules', function (): void {
        $request = new ChangePasswordRequest();
        $rules = $request->rules();

        expect($rules)->toHaveKey('current_password');
        expect($rules)->toHaveKey('new_password');
        expect($rules)->toHaveKey('new_password_confirmation');

        expect($rules['current_password'])->toContain('required');
        expect($rules['current_password'])->toContain('string');

        expect($rules['new_password'])->toContain('required');
        expect($rules['new_password'])->toContain('string');
        expect($rules['new_password'])->toContain('min:8');

        expect($rules['new_password_confirmation'])->toContain('required');
        expect($rules['new_password_confirmation'])->toContain('same:new_password');
    });

    test('defines correct validation messages', function (): void {
        $request = new ChangePasswordRequest();
        $messages = $request->messages();

        expect($messages)->toHaveKey('current_password.required');
        expect($messages)->toHaveKey('new_password.required');
        expect($messages)->toHaveKey('new_password.min');
        expect($messages)->toHaveKey('new_password_confirmation.required');
        expect($messages)->toHaveKey('new_password_confirmation.same');

        expect($messages['current_password.required'])->toBe('Current password is required.');
        expect($messages['new_password.required'])->toBe('New password is required.');
        expect($messages['new_password.min'])->toBe('New password must be at least 8 characters.');
        expect($messages['new_password_confirmation.required'])->toBe('Password confirmation is required.');
        expect($messages['new_password_confirmation.same'])->toBe('Password confirmation must match the new password.');
    });

    test('fails validation when fields are missing', function (): void {
        $invalidData = [
            'current_password' => 'oldpass',
            // Missing new_password and confirmation
        ];

        $_POST['current_password'] = $invalidData['current_password'];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new ChangePasswordRequest();

        expect(fn() => $request->validate())
            ->toThrow(\NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException::class);

        unset($_POST['current_password'], $_SERVER['REQUEST_METHOD']);
    });

    test('fails validation when passwords do not match', function (): void {
        $invalidData = [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'differentpassword123'
        ];

        $_POST['current_password'] = $invalidData['current_password'];
        $_POST['new_password'] = $invalidData['new_password'];
        $_POST['new_password_confirmation'] = $invalidData['new_password_confirmation'];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new ChangePasswordRequest();

        expect(fn() => $request->validate())
            ->toThrow(\NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException::class);

        unset($_POST['current_password'], $_POST['new_password'], $_POST['new_password_confirmation'], $_SERVER['REQUEST_METHOD']);
    });
});
