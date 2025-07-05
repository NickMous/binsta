<?php

use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\AbstractRoute;
use NickMous\Binsta\Internals\Routes\Route;

dataset('valid-routes', function () {
    $routes = include __DIR__ . '/valid-routes.php';

    // remove routes that contain VueResponse
    return array_filter($routes, function (AbstractRoute $route) {
        return !($route->path === '/vue-response');
    });
});
