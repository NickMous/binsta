<?php

use NickMous\Binsta\Controllers\AuthController;
use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::group('/api', [
        Route::group('/auth', [
            Route::post('/login', className: AuthController::class, methodName: 'login'),
            Route::post('/register', className: AuthController::class, methodName: 'register'),
            Route::get('/register', function () {
                return new VueResponse('auth/register');
            }),
        ])
    ]),
];
