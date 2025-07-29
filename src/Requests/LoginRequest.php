<?php

namespace NickMous\Binsta\Requests;

use NickMous\Binsta\Internals\Requests\Request;
use NickMous\Binsta\Internals\Validation\HasValidation;

class LoginRequest extends Request implements HasValidation
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
}
