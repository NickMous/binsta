<?php

use NickMous\Binsta\Controllers\AuthController;
use NickMous\Binsta\Controllers\LikeController;
use NickMous\Binsta\Controllers\PostController;
use NickMous\Binsta\Controllers\ProfileController;
use NickMous\Binsta\Controllers\SearchController;
use NickMous\Binsta\Controllers\UserController;
use NickMous\Binsta\Controllers\UserFollowController;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::group('/api', [
        Route::get('/{any:.*}', function () {
            return new JsonResponse([
                'error' => 'Not Found',
                'message' => 'The requested resource could not be found.'
            ], 404);
        }),
        Route::group('/auth', [
            Route::post('/login', className: AuthController::class, methodName: 'login'),
            Route::post('/register', className: AuthController::class, methodName: 'register'),
            Route::post('/logout', className: AuthController::class, methodName: 'logout'),
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
        Route::group('/users', [
            Route::get('/{user:.*}/statistics', className: UserController::class, methodName: 'statistics'),
            Route::get('/{user:.*}', className: UserController::class, methodName: 'show'),
            Route::get('/{userId:\d+}/posts', className: PostController::class, methodName: 'byUser'),
            Route::get('/{userId:\d+}/follow-status', className: UserFollowController::class, methodName: 'followStatus'),
            Route::post('/{userId:\d+}/follow', className: UserFollowController::class, methodName: 'follow'),
            Route::post('/{userId:\d+}/unfollow', className: UserFollowController::class, methodName: 'unfollow'),
        ]),
        Route::group('/posts', [
            Route::get('/', className: PostController::class, methodName: 'index'),
            Route::get('/feed', className: PostController::class, methodName: 'personalFeed'),
            Route::post('/', className: PostController::class, methodName: 'store'),
            Route::get('/{post:\d+}', className: PostController::class, methodName: 'show'),
            Route::put('/{post:\d+}', className: PostController::class, methodName: 'update'),
            Route::delete('/{post:\d+}', className: PostController::class, methodName: 'destroy'),
            Route::get('/{post:\d+}/like-status', className: LikeController::class, methodName: 'status'),
            Route::post('/{post:\d+}/like', className: LikeController::class, methodName: 'like'),
            Route::post('/{post:\d+}/unlike', className: LikeController::class, methodName: 'unlike'),
            Route::get('/language/{language}', className: PostController::class, methodName: 'byLanguage'),
            Route::get('/search/{query}', className: PostController::class, methodName: 'search'),
        ]),
        Route::get('/search/{query}', className: SearchController::class, methodName: 'search'),
    ]),
];
