<?php

use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::get('/empty-content', function () {
        return new Response("");
    }),
];
