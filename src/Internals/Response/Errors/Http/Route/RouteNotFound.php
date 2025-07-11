<?php

namespace NickMous\Binsta\Internals\Response\Errors\Http\Route;

use NickMous\Binsta\Internals\Response\Errors\Error;

class RouteNotFound extends Error
{
    public function __construct(string $routeName, string $method)
    {
        parent::__construct("Route not found: {$method}: {$routeName}", 404);
    }
}
