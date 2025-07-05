<?php

use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::get('/invalid-response', function () {
        // This closure does not return a Response instance, which is invalid.
        return 'This is an invalid response for the route: /invalid-response';
    })
];
