<?php

use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::group('/api', [
        Route::get('/users', function () {
            return new Response('Users list');
        }),
        Route::get('/posts', function () {
            return new Response('Posts list');
        }),
    ]),
    Route::get('/standalone', function () {
        return new Response('Standalone route');
    }),
];
