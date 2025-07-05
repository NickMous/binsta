<?php

use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\Route;
use NickMous\Binsta\Internals\Services\VueService;

return [
    Route::get('/', function () {
        return new Response('This is the response for the route: /');
    }),
    Route::get('/about', function () {
        return new Response('This is the response for the route: /about');
    }),
    Route::get('/contact', function () {
        return new Response('This is the response for the route: /contact');
    }),
    Route::get('/with-headers', function () {
        $response = new Response('This is the response for the route: /with-headers');
        $response->headers = ['Content-type' => 'text/plain'];
        return $response;
    }),
    Route::get('/vue-response', function () {
        return new VueResponse('HelloWorld');
    })
];
