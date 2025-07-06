<?php

use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::get('/', function () {
        return new VueResponse('HomePage');
    }),
];
