<?php

namespace NickMous\Binsta\Internals\Routes\Type;

use NickMous\Binsta\Internals\Routes\AbstractRoute;

class Group
{
    /**
     * @param string                     $path
     * @param array<AbstractRoute|Group> $routes
     */
    public function __construct(
        public string $path,
        public array $routes = [],
    ) {
    }
}
