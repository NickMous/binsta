<?php

namespace NickMous\Binsta\Internals\Services;

use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Exceptions\Route\InvalidRouteClassException;
use NickMous\Binsta\Internals\Exceptions\Route\NoObjectException;
use NickMous\Binsta\Internals\Response\Errors\Error;
use NickMous\Binsta\Internals\Response\Errors\Http\Route\RouteNotFound;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\AbstractRoute;

class ControllerService
{
    /**
     * @var array<string, mixed>
     */
    private array $routes = [];

    /**
     * @throws NoObjectException
     * @throws InvalidRouteClassException
     */
    public function __construct()
    {
        $this->getRoutes();
    }

    /**
     * @throws NoObjectException
     * @throws InvalidRouteClassException
     */
    private function getRoutes(): void
    {
        $routes = include __DIR__ . '/../../../routes/web.php';

        foreach ($routes as $route) {
            if (!is_object($route)) {
                throw new NoObjectException();
            }

            if (!is_subclass_of($route, AbstractRoute::class)) {
                throw new InvalidRouteClassException($route::class);
            }

            $this->routes[$route->path] = $route;
        }
    }

    /**
     * @throws InvalidResponseException
     */
    public function callRoute(string $route): void
    {
        if (!isset($this->routes[$route])) {
            $this->handleResponse(new RouteNotFound($route));
        }

        $routeObject = $this->routes[$route];

        assert($routeObject instanceof AbstractRoute);
        $response = $routeObject->handle();
        $this->handleResponse($response);
    }

    private function handleResponse(Response $response): void
    {
        http_response_code($response->statusCode);

        if (!headers_sent()) {
            foreach ($response->headers as $header) {
                header($header);
            }
        }

        if (!empty($response->content)) {
            echo $response->content;
        }
    }
}
