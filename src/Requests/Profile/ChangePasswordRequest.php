<?php

namespace NickMous\Binsta\Requests\Profile;

use NickMous\Binsta\Internals\Requests\Request;
use NickMous\Binsta\Internals\Validation\HasValidation;

class ChangePasswordRequest extends Request implements HasValidation
{
    /**
     * @return array|string[]|string[][]
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|string|same:new_password',
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
            'current_password.required' => 'Current password is required.',
            'current_password.string' => 'Current password must be a string.',
            'new_password.required' => 'New password is required.',
            'new_password.string' => 'New password must be a string.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password_confirmation.required' => 'Password confirmation is required.',
            'new_password_confirmation.string' => 'Password confirmation must be a string.',
            'new_password_confirmation.same' => 'Password confirmation must match the new password.',
        ];
    }
}
