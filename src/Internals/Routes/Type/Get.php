<?php

namespace NickMous\Binsta\Internals\Routes\Type;

use Closure;
use NickMous\Binsta\Internals\Routes\AbstractRoute;

class Get extends AbstractRoute
{
    /**
     * Get constructor.
     *
     * @param string  $path
     * @param Closure $closure
     */
    public function __construct(
        string $path,
        Closure $closure
    ) {
        parent::__construct($path, $closure, 'GET');
    }
}
