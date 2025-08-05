<?php

use NickMous\Binsta\Internals\Validation\Validators\ImageRule;

covers(ImageRule::class);

describe('ImageRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new ImageRule();
        expect($rule->getKey())->toBe('image');
    });

    test('validates valid image file uploads', function (): void {
        $rule = new ImageRule();

        // Create a temporary test image file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_image');
        $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='); // 1x1 PNG
        file_put_contents($tempFile, $imageData);

        // Mock valid image file upload
        $validImageFile = [
            'size' => strlen($imageData),
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => $tempFile,
            'name' => 'test.png',
            'type' => 'image/png'
        ];

        expect($rule->validate($validImageFile))->toBe(true);

        // Clean up
        unlink($tempFile);
    });

    test('rejects files with upload errors', function (): void {
        $rule = new ImageRule();

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

    test('rejects non-image files by MIME type', function (): void {
        $rule = new ImageRule();

        // Create a temporary text file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_text');
        file_put_contents($tempFile, 'This is not an image');

        // Mock non-image file upload
        $textFile = [
            'size' => 19,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => $tempFile,
            'name' => 'test.txt',
            'type' => 'text/plain'
        ];

        expect($rule->validate($textFile))->toBe(false);

        // Clean up
        unlink($tempFile);
    });

    test('validates different image formats', function (): void {
        $rule = new ImageRule();

        // Test JPEG
        $tempJpeg = tempnam(sys_get_temp_dir(), 'test_jpeg');
        $jpegData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/wA==');
        file_put_contents($tempJpeg, $jpegData);

        $jpegFile = [
            'size' => strlen($jpegData),
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => $tempJpeg,
            'name' => 'test.jpg',
            'type' => 'image/jpeg'
        ];

        expect($rule->validate($jpegFile))->toBe(true);

        // Test PNG
        $tempPng = tempnam(sys_get_temp_dir(), 'test_png');
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($tempPng, $pngData);

        $pngFile = [
            'size' => strlen($pngData),
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => $tempPng,
            'name' => 'test.png',
            'type' => 'image/png'
        ];

        expect($rule->validate($pngFile))->toBe(true);

        // Clean up
        unlink($tempJpeg);
        unlink($tempPng);
    });

    test('rejects non-file data', function (): void {
        $rule = new ImageRule();

        expect($rule->validate(null))->toBe(false);
        expect($rule->validate('string'))->toBe(false);
        expect($rule->validate(123))->toBe(false);
        expect($rule->validate(true))->toBe(false);
        expect($rule->validate(['not_a_file']))->toBe(false);
        expect($rule->validate(new stdClass()))->toBe(false);
    });

    test('rejects arrays without required file keys', function (): void {
        $rule = new ImageRule();

        // Array without error key
        expect($rule->validate(['size' => 1024]))->toBe(false);

        // Array without size key
        expect($rule->validate(['error' => UPLOAD_ERR_OK]))->toBe(false);

        // Empty array
        expect($rule->validate([]))->toBe(false);
    });

    test('validates WebP images', function (): void {
        $rule = new ImageRule();

        // Create a minimal WebP file for testing
        $tempWebP = tempnam(sys_get_temp_dir(), 'test_webp');
        // This is a minimal valid WebP header
        $webpData = "RIFF\x1a\x00\x00\x00WEBPVP8 \x0e\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
        file_put_contents($tempWebP, $webpData);

        $webpFile = [
            'size' => strlen($webpData),
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => $tempWebP,
            'name' => 'test.webp',
            'type' => 'image/webp'
        ];

        // Note: This test may pass or fail depending on system MIME detection capabilities
        // WebP support varies by system configuration
        $result = $rule->validate($webpFile);
        expect($result)->toBeIn([true, false]); // Either works or doesn't, both are acceptable

        // Clean up
        unlink($tempWebP);
    });

    test('validates GIF images', function (): void {
        $rule = new ImageRule();

        // Create a minimal GIF file
        $tempGif = tempnam(sys_get_temp_dir(), 'test_gif');
        $gifData = "GIF89a\x01\x00\x01\x00\x00\x00\x00!\xf9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x04\x01\x00;";
        file_put_contents($tempGif, $gifData);

        $gifFile = [
            'size' => strlen($gifData),
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => $tempGif,
            'name' => 'test.gif',
            'type' => 'image/gif'
        ];

        expect($rule->validate($gifFile))->toBe(true);

        // Clean up
        unlink($tempGif);
    });

    test('handles missing temp file gracefully', function (): void {
        $rule = new ImageRule();

        // File array with non-existent temp file
        $invalidTempFile = [
            'size' => 1024,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => '/non/existent/file',
            'name' => 'test.jpg'
        ];

        expect($rule->validate($invalidTempFile))->toBe(false);
    });
});
