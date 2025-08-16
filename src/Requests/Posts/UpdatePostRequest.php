<?php

namespace NickMous\Binsta\Requests\Posts;

use NickMous\Binsta\Internals\Requests\HasTransformation;
use NickMous\Binsta\Internals\Requests\Request;
use NickMous\Binsta\Internals\Validation\HasValidation;

class UpdatePostRequest extends Request implements HasValidation, HasTransformation
{
    /**
     * @return array|string[]|string[][]
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10|max:1000',
            'code' => 'required|string|min:1|max:10000',
            'programming_language' => 'required|string|in:javascript,typescript,php,python,java,html,css,json,bash,shell,c,cpp,csharp,go,rust,ruby,kotlin,swift,sql,yaml,xml,vue,jsx,tsx',
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
            'title.required' => 'Title is required.',
            'title.string' => 'Title must be a string.',
            'title.min' => 'Title must be at least 3 characters.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'description.required' => 'Description is required.',
            'description.string' => 'Description must be a string.',
            'description.min' => 'Description must be at least 10 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'code.required' => 'Code is required.',
            'code.string' => 'Code must be a string.',
            'code.min' => 'Code cannot be empty.',
            'code.max' => 'Code cannot exceed 10000 characters.',
            'programming_language.required' => 'Programming language is required.',
            'programming_language.string' => 'Programming language must be a string.',
            'programming_language.in' => 'Programming language must be a supported language.',
        ];
    }

    /**
     * Transform request data - sanitize and normalize
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function transform(array $data): array
    {
        // Trim whitespace from string fields
        if (isset($data['title']) && is_string($data['title'])) {
            $data['title'] = trim($data['title']);
        }

        if (isset($data['description']) && is_string($data['description'])) {
            $data['description'] = trim($data['description']);
        }

        // Normalize programming language to lowercase
        if (isset($data['programming_language']) && is_string($data['programming_language'])) {
            $data['programming_language'] = strtolower(trim($data['programming_language']));
        }

        return $data;
    }
}
