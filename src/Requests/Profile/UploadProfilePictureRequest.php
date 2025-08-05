<?php

namespace NickMous\Binsta\Requests\Profile;

use NickMous\Binsta\Internals\Requests\Request;
use NickMous\Binsta\Internals\Validation\HasValidation;

class UploadProfilePictureRequest extends Request implements HasValidation
{
    /**
     * @return array|string[]|string[][]
     */
    public function rules(): array
    {
        return [
            'profile_picture' => 'required|file|image|max:2048', // 2MB max
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
            'profile_picture.required' => 'Profile picture is required.',
            'profile_picture.file' => 'Profile picture must be a file.',
            'profile_picture.image' => 'Profile picture must be an image.',
            'profile_picture.max' => 'Profile picture must be less than 2MB.',
        ];
    }
}
