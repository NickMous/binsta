<?php

namespace NickMous\Binsta\Internals\Routes\Type;

use NickMous\Binsta\Internals\Routes\AbstractRoute;

class Delete extends AbstractRoute
{
    public function __construct(
        string $path,
        ?\Closure $closure = null,
        ?string $className = null,
        ?string $methodName = null,
    ) {
        parent::__construct($path, $closure, $className, $methodName, 'DELETE');
    }
}
