<?php

use NickMous\Binsta\Internals\Response\Response;

covers(Response::class);

describe('Response', function (): void {
    test('creates response with default values', function (): void {
        $response = new Response();

        expect($response->content)->toBe("");
        expect($response->statusCode)->toBe(200);
        expect($response->headers)->toBe([]);
    });

    test('creates response with content only', function (): void {
        $response = new Response('Hello World');

        expect($response->content)->toBe('Hello World');
        expect($response->statusCode)->toBe(200);
        expect($response->headers)->toBe([]);
    });

    test('creates response with content and status code', function (): void {
        $response = new Response('Not Found', 404);

        expect($response->content)->toBe('Not Found');
        expect($response->statusCode)->toBe(404);
        expect($response->headers)->toBe([]);
    });

    test('creates response with all parameters', function (): void {
        $headers = ['Content-Type' => 'text/html', 'X-Custom' => 'value'];
        $response = new Response('Custom Content', 201, $headers);

        expect($response->content)->toBe('Custom Content');
        expect($response->statusCode)->toBe(201);
        expect($response->headers)->toBe($headers);
    });

    test('properties are public and can be modified', function (): void {
        $response = new Response();

        $response->content = 'Modified Content';
        $response->statusCode = 500;
        $response->headers = ['Error' => 'true'];

        expect($response->content)->toBe('Modified Content');
        expect($response->statusCode)->toBe(500);
        expect($response->headers)->toBe(['Error' => 'true']);
    });

    test('handles empty string content explicitly', function (): void {
        $response = new Response('', 204);

        expect($response->content)->toBe('');
        expect($response->statusCode)->toBe(204);
    });

    test('handles complex headers array', function (): void {
        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'no-cache',
            'X-Response-Time' => '100ms'
        ];

        $response = new Response('{"success": true}', 200, $headers);

        expect($response->headers)->toBe($headers);
        expect($response->headers['Content-Type'])->toBe('application/json');
        expect($response->headers['X-Response-Time'])->toBe('100ms');
    });
});
