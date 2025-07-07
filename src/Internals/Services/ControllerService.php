<?php

namespace NickMous\Binsta\Internals\Services;

use NickMous\Binsta\Internals\Exceptions\InvalidRouteFileGiven;
use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Exceptions\Route\InvalidRouteClassException;
use NickMous\Binsta\Internals\Exceptions\Route\NoObjectException;
use NickMous\Binsta\Internals\Response\Errors\Http\Route\RouteNotFound;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\AbstractRoute;

class ControllerService
{
    /**
     * @var array<string, mixed>
     */
    private array $routes = [];

    /**
     * @var array<string, string>
     */
    private array $routePatterns = [];

    /**
     * @param string ...$routePatterns
     * @throws InvalidRouteClassException
     * @throws InvalidRouteFileGiven
     * @throws NoObjectException
     */
    public function __construct(string ...$routePatterns)
    {
        foreach ($routePatterns as $routeFilePath) {
            $this->loadRoutes($routeFilePath);
        }
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
            $this->routePatterns[$route->path] = $this->convertToRegex($route->path);
        }
    }

    /**
     * @throws InvalidResponseException
     */
    public function callRoute(string $route): void
    {
        // First try exact match for performance
        if (isset($this->routes[$route]) && ($this->routes[$route] instanceof AbstractRoute)) {
            $routeObject = $this->routes[$route];
            $response = $routeObject->handle();
            $this->handleResponse($response);
            return;
        }

        // Try pattern matching
        foreach ($this->routePatterns as $pattern => $regex) {
            if (preg_match($regex, $route, $matches)) {
                $routeObject = $this->routes[$pattern];

                // Extract route parameters and make them available
                $parameters = $this->extractParameters($matches);
                $this->setRouteParameters($parameters);

                $response = $routeObject->handle();
                $this->handleResponse($response);
                return;
            }
        }

        // No route found
        $this->handleResponse(new RouteNotFound($route));
    }

    /**
     * Convert route pattern to regex
     */
    private function convertToRegex(string $pattern): string
    {
        // Replace route parameters with regex groups
        $regex = preg_replace_callback('#{([^:}]+):([^}]+)}#', function (array $matches) {
            return '(?P<' . $matches[1] . '>' . $matches[2] . ')';
        }, $pattern);

        $regex = preg_replace('#{([^}]+)}#', '(?P<$1>[^/]+)', $regex);

        // Escape literal parts but preserve regex groups
        $parts = preg_split('/(\(\?P<[^>]+>[^)]+\))/', $regex, -1, PREG_SPLIT_DELIM_CAPTURE);
        $escaped = '';
        foreach ($parts as $part) {
            $escaped .= preg_match('/^\(\?P</', $part) ? $part : preg_quote($part, '#');
        }

        return '#^' . $escaped . '$#';
    }

    /**
     * Extract named parameters from regex matches
     *
     * @param array<int|string, mixed> $matches
     * @return array<string, string>
     */
    private function extractParameters(array $matches): array
    {
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Store route parameters globally for access in closures
     *
     * @param array<string, string> $parameters
     */
    private function setRouteParameters(array $parameters): void
    {
        $GLOBALS['route_parameters'] = $parameters;
    }

    private function handleResponse(Response $response): void
    {
        http_response_code($response->statusCode);

        if (!headers_sent()) {
            foreach ($response->headers as $headerKey => $headerData) {
                header("{$headerKey}: {$headerData}");
            }
        }

        if ($response instanceof VueResponse && !empty($response->componentName)) {
            $response = new ViteService()->process($response);
        }

        if (!empty($response->content)) {
            echo $response->content;
        } else {
            throw new InvalidResponseException("The response content is empty.");
        }
    }
}
