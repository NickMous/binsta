<?php

namespace NickMous\Binsta\Requests\Auth;

use NickMous\Binsta\Internals\Requests\HasTransformation;
use NickMous\Binsta\Internals\Requests\Request;
use NickMous\Binsta\Internals\Validation\HasValidation;

class RegisterRequest extends Request implements HasValidation, HasTransformation
{
    /**
     * @return array|string[]|string[][]
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
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
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'Email address is already registered.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 8 characters.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.string' => 'Password confirmation must be a string.',
            'password_confirmation.same' => 'Password confirmation must match the password.',
        ];
    }

    /**
     * Transform request data - convert email to lowercase
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

        return $data;
    }
}
