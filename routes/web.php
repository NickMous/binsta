<?php

use NickMous\Binsta\Controllers\TestController;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::get('/', function () {
        return new Response('Hello World');
    }),
    Route::get('/test', function () {
        return new TestController()->index();
    }),
];
