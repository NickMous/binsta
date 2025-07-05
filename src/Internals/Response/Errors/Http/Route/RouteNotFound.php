<?php

namespace NickMous\Binsta\Internals\Response\Errors\Http\Route;

use NickMous\Binsta\Internals\Response\Errors\Error;

class RouteNotFound extends Error
{
    public function __construct(string $routeName)
    {
        parent::__construct("Route not found: {$routeName}", 404);
    }
}
