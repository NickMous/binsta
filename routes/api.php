<?php

use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\Route;

return [
    // API endpoints
    Route::get('/api/posts', function () {
        // Example API response - you can replace with proper JSON response
        return new VueResponse('API: Posts endpoint');
    }),
    
    Route::get('/api/posts/{id:\d+}', function () {
        $params = $GLOBALS['route_parameters'] ?? [];
        return new VueResponse('API: Post ' . ($params['id'] ?? 'unknown'));
    }),
    
    Route::get('/api/users', function () {
        return new VueResponse('API: Users endpoint');
    }),
    
    Route::get('/api/users/{id:\d+}', function () {
        $params = $GLOBALS['route_parameters'] ?? [];
        return new VueResponse('API: User ' . ($params['id'] ?? 'unknown'));
    }),
];