<?php

namespace NickMous\Binsta\Requests\Profile;

use NickMous\Binsta\Internals\Requests\HasTransformation;
use NickMous\Binsta\Internals\Requests\Request;
use NickMous\Binsta\Internals\Validation\HasValidation;

class UpdateProfileRequest extends Request implements HasValidation, HasTransformation
{
    /**
     * @return array|string[]|string[][]
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|regex:/^[a-zA-Z0-9_]+$/|min:3|max:20',
            'email' => 'sometimes|required|email|max:255',
            'biography' => 'sometimes|nullable|string|max:500',
        ];
    }

    /**
     * Define the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'username.required' => 'Username is required.',
            'username.string' => 'Username must be a string.',
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username cannot exceed 20 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'biography.string' => 'Biography must be a string.',
            'biography.max' => 'Biography cannot exceed 500 characters.',
        ];
    }

    /**
     * Transform request data - clean up input
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function transform(array $data): array
    {
        // Convert email to lowercase for consistency
        if (isset($data['email']) && is_string($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        // Trim name and username
        if (isset($data['name']) && is_string($data['name'])) {
            $data['name'] = trim($data['name']);
        }

        if (isset($data['username']) && is_string($data['username'])) {
            $data['username'] = trim($data['username']);
        }

        // Trim biography
        if (isset($data['biography']) && is_string($data['biography'])) {
            $data['biography'] = trim($data['biography']);
            // Convert empty string to null
            if ($data['biography'] === '') {
                $data['biography'] = null;
            }
        }

        return $data;
    }
}
