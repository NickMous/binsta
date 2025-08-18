<?php

namespace NickMous\Binsta\Requests\Comments;

use NickMous\Binsta\Internals\Requests\HasTransformation;
use NickMous\Binsta\Internals\Requests\Request;
use NickMous\Binsta\Internals\Validation\HasValidation;

class CreateCommentRequest extends Request implements HasValidation, HasTransformation
{
    /**
     * @return array|string[]|string[][]
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string|min:1|max:1000',
            'post_id' => 'required|integer',
            'user_id' => 'required|integer',
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
            'content.required' => 'Comment content is required.',
            'content.string' => 'Comment content must be a string.',
            'content.min' => 'Comment cannot be empty.',
            'content.max' => 'Comment cannot exceed 1000 characters.',
            'post_id.required' => 'Post ID is required.',
            'post_id.integer' => 'Post ID must be an integer.',
            'user_id.required' => 'User ID is required.',
            'user_id.integer' => 'User ID must be an integer.',
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
        // Trim whitespace from content
        if (isset($data['content']) && is_string($data['content'])) {
            $data['content'] = trim($data['content']);
        }

        // Ensure IDs are integers
        if (isset($data['post_id'])) {
            $data['post_id'] = (int) $data['post_id'];
        }

        if (isset($data['user_id'])) {
            $data['user_id'] = (int) $data['user_id'];
        }

        return $data;
    }
}
