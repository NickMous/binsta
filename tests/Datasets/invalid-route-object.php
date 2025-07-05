<?php

use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::get('/', function () {
        return new Response('Home');
    }),
    'About'
];
