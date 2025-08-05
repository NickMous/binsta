<?php

namespace NickMous\Binsta\Internals\Validation\Validators;

use NickMous\Binsta\Internals\Validation\ValidationRule;

class ImageRule implements ValidationRule
{
    public const string KEY = 'image';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function validate(mixed $value): bool
    {
        // Value should be the file array from $_FILES
        if (!is_array($value) || !isset($value['error'], $value['size'], $value['tmp_name']) || $value['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Ensure temp file exists
        if (empty($value['tmp_name']) || !file_exists($value['tmp_name'])) {
            return false;
        }

        // Check if file is an image by MIME type
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        $mimeType = mime_content_type($value['tmp_name']);
        return in_array($mimeType, $allowedMimeTypes);
    }
}
