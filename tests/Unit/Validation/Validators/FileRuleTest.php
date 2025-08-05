<?php

use NickMous\Binsta\Internals\Validation\Validators\FileRule;

covers(FileRule::class);

describe('FileRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new FileRule();
        expect($rule->getKey())->toBe('file');
    });

    test('validates successful file uploads', function (): void {
        $rule = new FileRule();

        // Mock successful file upload
        $validFile = [
            'size' => 1024,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => '/tmp/phpupload',
            'name' => 'test.jpg'
        ];

        expect($rule->validate($validFile))->toBe(true);
    });

    test('rejects files with upload errors', function (): void {
        $rule = new FileRule();

        // Mock file with upload error
        $errorFile = [
            'size' => 1024,
            'error' => UPLOAD_ERR_NO_FILE,
            'tmp_name' => '',
            'name' => ''
        ];

        expect($rule->validate($errorFile))->toBe(false);

        // Test other upload errors
        $sizeErrorFile = [
            'size' => 0,
            'error' => UPLOAD_ERR_FORM_SIZE,
            'tmp_name' => '',
            'name' => 'large.jpg'
        ];

        expect($rule->validate($sizeErrorFile))->toBe(false);
    });

    test('rejects non-file data', function (): void {
        $rule = new FileRule();

        expect($rule->validate(null))->toBe(false);
        expect($rule->validate('string'))->toBe(false);
        expect($rule->validate(123))->toBe(false);
        expect($rule->validate(true))->toBe(false);
        expect($rule->validate(['not_a_file']))->toBe(false);
        expect($rule->validate(new stdClass()))->toBe(false);
    });

    test('rejects arrays without required file keys', function (): void {
        $rule = new FileRule();

        // Array without error key
        expect($rule->validate(['size' => 1024]))->toBe(false);

        // Array without size key
        expect($rule->validate(['error' => UPLOAD_ERR_OK]))->toBe(false);

        // Empty array
        expect($rule->validate([]))->toBe(false);
    });
});
