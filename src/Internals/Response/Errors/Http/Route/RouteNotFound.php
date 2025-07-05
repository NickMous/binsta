<?php

namespace NickMous\Binsta\Internals\Response\Errors\Http\Route;

use NickMous\Binsta\Internals\Response\Errors\Error;

class RouteNotFound extends Error
{
    public string $message = 'Route not found';
    public function __construct(string $routeName)
    {
        $this->message = "Route not found: {$routeName}";
        parent::__construct("Error", 404);
    }
}
