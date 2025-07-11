<?php

use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::group('/api', [
        Route::group('/auth', [
            Route::get('/login', function () {
                return new VueResponse('auth/login');
            }),
            Route::get('/register', function () {
                return new VueResponse('auth/register');
            }),
        ])
    ]),
];