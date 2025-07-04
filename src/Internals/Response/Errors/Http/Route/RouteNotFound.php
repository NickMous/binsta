<?php

namespace NickMous\Binsta\Internals\Response\Errors\Http\Route;

use NickMous\Binsta\Internals\Response\Errors\Error;

class RouteNotFound extends Error
{
    public function __construct(string $routeName)
    {
        $message = "Route not found: {$routeName}";
        parent::__construct($message, 404);
    }
}
