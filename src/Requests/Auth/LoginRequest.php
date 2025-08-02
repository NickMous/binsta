<?php

namespace NickMous\Binsta\Requests\Auth;

use NickMous\Binsta\Internals\Requests\HasTransformation;
use NickMous\Binsta\Internals\Requests\Request;
use NickMous\Binsta\Internals\Validation\HasValidation;

class LoginRequest extends Request implements HasValidation, HasTransformation
{
    /**
     * @return array|string[]|string[][]
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
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
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
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
