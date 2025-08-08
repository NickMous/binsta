<?php

use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\Route;

return [
    // SPA - serve Vue app for all routes
    Route::get('/{path:.*}', function () {
        return new VueResponse('App', [
            'user' => $_SESSION['user'] ?? 0,
        ]);
    }),
];
