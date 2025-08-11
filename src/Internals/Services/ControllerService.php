<?php

namespace NickMous\Binsta\Internals\Services;

use NickMous\Binsta\Internals\Exceptions\InvalidRouteFileGiven;
use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Exceptions\Route\InvalidRouteClassException;
use NickMous\Binsta\Internals\Exceptions\Route\NoObjectException;
use NickMous\Binsta\Internals\Exceptions\Validation\ValidationFailedException;
use NickMous\Binsta\Internals\Response\Errors\Http\Route\RouteNotFound;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Routes\AbstractRoute;
use NickMous\Binsta\Internals\Routes\Type\Group;

class ControllerService
{
    /**
     * @var array<string, array<string, AbstractRoute>>
     */
    private array $routes = [];

    /**
     * @var array<string, array<string, string>>
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

        $this->sortRoutes();
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

            if (!is_subclass_of($route, AbstractRoute::class) && !$route instanceof Group) {
                throw new InvalidRouteClassException($route::class);
            }

            $this->loadRoute($route);
        }
    }

    private function loadRoute(AbstractRoute|Group $route, string $prefix = ''): void
    {
        if ($route instanceof Group) {
            foreach ($route->routes as $subRoute) {
                $this->loadRoute($subRoute, $prefix . $route->path);
            }
        } else {
            if (!isset($this->routes[$route->method])) {
                $this->routes[$route->method] = [];
                $this->routePatterns[$route->method] = [];
            }

            $fullPath = rtrim($prefix . $route->path, '/') ?: '/';
            $this->routes[$route->method][$fullPath] = $route;
            $this->routePatterns[$route->method][$fullPath] = $this->convertToRegex($fullPath);
        }
    }

    private function sortRoutes(): void
    {
        foreach ($this->routes as $method => $routes) {
            uasort($routes, function ($a, $b) {
                $aPath = $a->path;
                $bPath = $b->path;

                // Catch-all routes (containing .*) should be last
                $aIsCatchAll = str_contains($aPath, '.*');
                $bIsCatchAll = str_contains($bPath, '.*');

                if ($aIsCatchAll && !$bIsCatchAll) {
                    return 1;  // a goes after b
                }
                if (!$aIsCatchAll && $bIsCatchAll) {
                    return -1; // a goes before b
                }

                // If both are catch-all or both are specific, sort by specificity
                // More specific routes (more slashes, fewer parameters) go first
                $aSlashCount = substr_count($aPath, '/');
                $bSlashCount = substr_count($bPath, '/');

                if ($aSlashCount !== $bSlashCount) {
                    return $bSlashCount <=> $aSlashCount; // More slashes first
                }

                // If same slash count, routes with fewer parameters are more specific
                $aParamCount = substr_count($aPath, '{');
                $bParamCount = substr_count($bPath, '{');

                return $aParamCount <=> $bParamCount; // Fewer parameters first
            });

            $this->routes[$method] = $routes;

            // Also sort the routePatterns to match
            $sortedPatterns = [];
            foreach ($routes as $path => $route) {
                $sortedPatterns[$path] = $this->routePatterns[$method][$path];
            }
            $this->routePatterns[$method] = $sortedPatterns;
        }
    }

    /**
     * @throws InvalidResponseException
     */
    public function callRoute(string $route, string $method): void
    {
        // Check if method exists at all
        if (!isset($this->routes[$method])) {
            $this->handleResponse(new RouteNotFound($route, $method));
            return;
        }

        // First try exact match for performance
        if (isset($this->routes[$method][$route])) {
            $routeObject = $this->routes[$method][$route];
            $this->handleRouteObject($routeObject);
            return;
        }

        // Try pattern matching
        if (isset($this->routePatterns[$method])) {
            foreach ($this->routePatterns[$method] as $pattern => $regex) {
                if (preg_match($regex, $route, $matches)) {
                    $routeObject = $this->routes[$method][$pattern];

                    // Extract route parameters and make them available
                    $parameters = $this->extractParameters($matches);
                    $this->setRouteParameters($parameters);

                    $this->handleRouteObject($routeObject);
                    return;
                }
            }
        }

        // No route found
        $this->handleResponse(new RouteNotFound($route, $method));
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

    private function handleRouteObject(AbstractRoute $routeObject): void
    {
        try {
            $response = $routeObject->handle();
            $this->handleResponse($response);
        } catch (ValidationFailedException $e) {
            if ($e->returnJson === true) {
                $response = new JsonResponse(
                    data: [
                        'error' => 'Validation failed',
                        'message' => $e->getMessage(),
                        'fields' => $e->errors,
                    ],
                    status: 400
                );
                $this->handleResponse($response);
            }
        }
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
