<?php

use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::get('/api/test', function () {
        return new Response('API test endpoint');
    }),
    Route::get('/api/users/{id:\d+}', function () {
        $params = $GLOBALS['route_parameters'] ?? [];
        return new Response('User ID: ' . ($params['id'] ?? 'unknown'));
    }),
    Route::get('/api/posts/{slug:[a-z-]+}', function () {
        $params = $GLOBALS['route_parameters'] ?? [];
        return new Response('Post slug: ' . ($params['slug'] ?? 'unknown'));
    }),
    Route::get('/api/catch/{path:.*}', function () {
        $params = $GLOBALS['route_parameters'] ?? [];
        return new Response('Catch-all path: ' . ($params['path'] ?? 'empty'));
    }),
];
