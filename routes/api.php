<?php

use NickMous\Binsta\Controllers\AuthController;
use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::group('/api', [
        Route::group('/auth', [
            Route::get('/login', className: AuthController::class, methodName: 'login'),
            Route::get('/register', function () {
                return new VueResponse('auth/register');
            }),
        ])
    ]),
];
