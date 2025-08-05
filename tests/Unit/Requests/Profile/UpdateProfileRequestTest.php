<?php

use NickMous\Binsta\Requests\Profile\UpdateProfileRequest;

covers(UpdateProfileRequest::class);

describe('UpdateProfileRequest', function (): void {
    beforeEach(function (): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
    });

    afterEach(function (): void {
        unset($_SERVER['REQUEST_METHOD']);
    });

    test('defines correct validation rules', function (): void {
        $request = new UpdateProfileRequest();
        $rules = $request->rules();

        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('username');
        expect($rules)->toHaveKey('email');
        expect($rules)->toHaveKey('biography');

        expect($rules['name'])->toContain('required');
        expect($rules['name'])->toContain('string');
        expect($rules['name'])->toContain('max:255');

        expect($rules['username'])->toContain('required');
        expect($rules['username'])->toContain('string');
        expect($rules['username'])->toContain('min:3');
        expect($rules['username'])->toContain('max:20');
        expect($rules['username'])->toContain('regex:/^[a-zA-Z0-9_]+$/');

        expect($rules['email'])->toContain('required');
        expect($rules['email'])->toContain('email');

        expect($rules['biography'])->toContain('string');
        expect($rules['biography'])->toContain('max:500');
    });

    test('defines correct validation messages', function (): void {
        $request = new UpdateProfileRequest();
        $messages = $request->messages();

        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('username.required');
        expect($messages)->toHaveKey('username.min');
        expect($messages)->toHaveKey('username.max');
        expect($messages)->toHaveKey('username.regex');
        expect($messages)->toHaveKey('email.required');
        expect($messages)->toHaveKey('email.email');
        expect($messages)->toHaveKey('biography.max');

        expect($messages['name.required'])->toBe('Name is required.');
        expect($messages['username.required'])->toBe('Username is required.');
        expect($messages['username.min'])->toBe('Username must be at least 3 characters.');
        expect($messages['username.max'])->toBe('Username cannot exceed 20 characters.');
        expect($messages['username.regex'])->toBe('Username can only contain letters, numbers, and underscores.');
        expect($messages['email.required'])->toBe('Email is required.');
        expect($messages['email.email'])->toBe('Email must be a valid email address.');
        expect($messages['biography.max'])->toBe('Biography cannot exceed 500 characters.');
    });

    test('transform method processes data correctly', function (): void {
        $request = new UpdateProfileRequest();

        $data = [
            'name' => '  John Doe  ',
            'username' => '  johndoe  ',
            'email' => '  JOHN@EXAMPLE.COM  ',
            'biography' => '  This is a bio  '
        ];

        $transformed = $request->transform($data);

        expect($transformed['name'])->toBe('John Doe');
        expect($transformed['username'])->toBe('johndoe');
        expect($transformed['email'])->toBe('john@example.com');
        expect($transformed['biography'])->toBe('This is a bio');
    });

    test('transform method converts empty biography to null', function (): void {
        $request = new UpdateProfileRequest();

        $data = [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'biography' => '   '
        ];

        $transformed = $request->transform($data);

        expect($transformed['biography'])->toBeNull();
    });

    test('fails validation when required fields have invalid data', function (): void {
        $invalidData = [
            'name' => '', // Empty required field
            'username' => 'johndoe',
            'email' => 'john@example.com'
        ];

        $_POST['name'] = $invalidData['name'];
        $_POST['username'] = $invalidData['username'];
        $_POST['email'] = $invalidData['email'];

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new UpdateProfileRequest();

        expect(fn() => $request->validate())
            ->toThrow(\NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException::class);

        unset($_POST['name'], $_POST['username'], $_POST['email'], $_SERVER['REQUEST_METHOD']);
    });
});
