<?php

use NickMous\Binsta\Controllers\UserController;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::get('/api/users/{user:\d+}', className: UserController::class, methodName: 'show'),
];
