<?php

namespace NickMous\Binsta\Internals\Routes;

use Closure;

class Route
{
    public static function get(string $path, Closure $closure): Type\Get
    {
        return new Type\Get($path, $closure);
    }
}
