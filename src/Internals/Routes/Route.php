<?php

namespace NickMous\Binsta\Internals\Routes;

use Closure;
use NickMous\Binsta\Internals\Routes\Type\Group;

class Route
{
    public static function get(
        string $path,
        ?Closure $closure = null,
        ?string $className = null,
        ?string $methodName = null,
    ): Type\Get {
        return new Type\Get($path, $closure, $className, $methodName);
    }

    public static function post(
        string $path,
        ?Closure $closure = null,
        ?string $className = null,
        ?string $methodName = null,
    ): Type\Post {
        return new Type\Post($path, $closure, $className, $methodName);
    }

    public static function put(
        string $path,
        ?Closure $closure = null,
        ?string $className = null,
        ?string $methodName = null,
    ): Type\Put {
        return new Type\Put($path, $closure, $className, $methodName);
    }

    public static function delete(
        string $path,
        ?Closure $closure = null,
        ?string $className = null,
        ?string $methodName = null,
    ): Type\Delete {
        return new Type\Delete($path, $closure, $className, $methodName);
    }

    /**
     * @param string                     $path
     * @param array<AbstractRoute|Group> $routes
     * @return Type\Group
     */
    public static function group(string $path, array $routes): Type\Group
    {
        return new Type\Group($path, $routes);
    }
}
