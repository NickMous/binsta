<?php

namespace NickMous\Binsta\Internals\Routes;

use Closure;
use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Response\Response;
use RuntimeException;

abstract class AbstractRoute
{
    public function __construct(
        public string     $path,
        protected Closure $closure,
        public string     $method,
    ) {
    }

    /**
     * @throws InvalidResponseException
     */
    public function handle(): Response
    {
        $closure = $this->closure;
        $response = $closure();

        if ($response instanceof Response) {
            return $response;
        }

        throw new InvalidResponseException();
    }
}
