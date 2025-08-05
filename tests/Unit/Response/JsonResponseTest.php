<?php

use NickMous\Binsta\Internals\Response\JsonResponse;

covers(JsonResponse::class);

describe('JsonResponse', function (): void {
    test('creates JSON response with array data', function (): void {
        $data = ['message' => 'Hello World', 'status' => 'success'];
        $response = new JsonResponse($data);

        expect($response->content)->toBe('{"message":"Hello World","status":"success"}');
        expect($response->statusCode)->toBe(200);
        expect($response->headers['Content-Type'])->toBe('application/json');
    });

    test('creates JSON response with custom status code', function (): void {
        $data = ['error' => 'Not Found'];
        $response = new JsonResponse($data, 404);

        expect($response->content)->toBe('{"error":"Not Found"}');
        expect($response->statusCode)->toBe(404);
        expect($response->headers['Content-Type'])->toBe('application/json');
    });

    test('creates JSON response with custom headers', function (): void {
        $data = ['data' => 'test'];
        $customHeaders = ['X-Custom-Header' => 'custom-value'];
        $response = new JsonResponse($data, 200, $customHeaders);

        expect($response->content)->toBe('{"data":"test"}');
        expect($response->statusCode)->toBe(200);
        expect($response->headers['Content-Type'])->toBe('application/json');
        expect($response->headers['X-Custom-Header'])->toBe('custom-value');
    });

    test('throws JsonException for invalid data', function (): void {
        // Create data that cannot be JSON encoded (circular reference)
        $data = [];
        $data['circular'] = &$data;

        expect(fn() => new JsonResponse($data))
            ->toThrow(JsonException::class);
    });

    test('handles empty array data', function (): void {
        $response = new JsonResponse([]);

        expect($response->content)->toBe('[]');
        expect($response->statusCode)->toBe(200);
        expect($response->headers['Content-Type'])->toBe('application/json');
    });

    test('handles nested array data', function (): void {
        $data = [
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'settings' => [
                    'theme' => 'dark',
                    'notifications' => true
                ]
            ]
        ];
        $response = new JsonResponse($data);

        $expectedJson = '{"user":{"id":1,"name":"John Doe","settings":{"theme":"dark","notifications":true}}}';
        expect($response->content)->toBe($expectedJson);
        expect($response->statusCode)->toBe(200);
        expect($response->headers['Content-Type'])->toBe('application/json');
    });
});
