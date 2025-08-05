<?php

namespace NickMous\Binsta\Internals\Routes\Type;

use Closure;
use NickMous\Binsta\Internals\Routes\AbstractRoute;

class Get extends AbstractRoute
{
    public function __construct(
        string $path,
        ?Closure $closure = null,
        ?string $className = null,
        ?string $methodName = null,
    ) {
        parent::__construct($path, $closure, $className, $methodName, 'GET');
    }
}
