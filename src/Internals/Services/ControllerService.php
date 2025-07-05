<?php

namespace NickMous\Binsta\Internals\Services;

use NickMous\Binsta\Internals\Exceptions\InvalidRouteFileGiven;
use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Exceptions\Route\InvalidRouteClassException;
use NickMous\Binsta\Internals\Exceptions\Route\NoObjectException;
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
    public function __construct(string $routeFilePath)
    {
        $this->loadRoutes($routeFilePath);
    }

    /**
     * @throws NoObjectException
     * @throws InvalidRouteClassException|InvalidRouteFileGiven
     */
    private function loadRoutes(string $routeFilePath): void
    {
        if (empty($routeFilePath) || !file_exists($routeFilePath)) {
            throw new InvalidRouteFileGiven($routeFilePath);
        }

        $routes = include $routeFilePath;

        if (!is_array($routes) || empty($routes)) {
            throw new InvalidRouteFileGiven("The given route file is empty: {$routeFilePath}");
        }

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
        if (!isset($this->routes[$route]) || !($this->routes[$route] instanceof AbstractRoute)) {
            $this->handleResponse(new RouteNotFound($route));
            return;
        }

        $routeObject = $this->routes[$route];
        $response = $routeObject->handle();
        $this->handleResponse($response);
    }

    private function handleResponse(Response $response): void
    {
        http_response_code($response->statusCode);

        if (!headers_sent()) {
            foreach ($response->headers as $headerKey => $headerData) {
                header("{$headerKey}: {$headerData}");
            }
        }

        if (!empty($response->componentName)) {
            echo new VueService()->render($response->componentName);
        }
    }
}
