<?php

namespace NickMous\Binsta\Internals\Routes;

use Closure;
use NickMous\Binsta\Internals\DependencyInjection\InjectionContainer;
use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Services\InjectionService;
use RuntimeException;

abstract class AbstractRoute
{
    public function __construct(
        public string $path,
        protected ?Closure $closure = null,
        protected ?string $className = null,
        protected ?string $methodName = null,
        public string $method = 'GET',
    ) {
    }

    /**
     * @throws InvalidResponseException
     */
    public function handle(): Response
    {
        if ($this->closure !== null) {
            return $this->handleClosure();
        }

        return $this->handleClassMethod();
    }

    private function handleClosure(): Response
    {
        $closure = $this->closure;
        $response = $closure();

        if ($response instanceof Response) {
            return $response;
        }

        throw new InvalidResponseException();
    }

    private function handleClassMethod(): Response
    {
        if ($this->className === null || $this->methodName === null) {
            throw new RuntimeException('Class name or method name is not set for this route.');
        }

        $class = new $this->className();
        if (!method_exists($class, $this->methodName)) {
            throw new RuntimeException("Method {$this->methodName} does not exist in class {$this->className}.");
        }

        $response = InjectionContainer::getInstance()->execute($this->className, $this->methodName);

        if ($response instanceof Response) {
            return $response;
        }

        throw new InvalidResponseException();
    }
}
