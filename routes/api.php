<?php

use NickMous\Binsta\Controllers\AuthController;
use NickMous\Binsta\Controllers\ProfileController;
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
        ]),
        Route::group('/profile', [
            Route::get('/', className: ProfileController::class, methodName: 'show'),
            Route::put('/', className: ProfileController::class, methodName: 'update'),
            Route::put('/password', className: ProfileController::class, methodName: 'changePassword'),
            Route::post('/picture', className: ProfileController::class, methodName: 'uploadProfilePicture'),
        ]),
    ]),
];
