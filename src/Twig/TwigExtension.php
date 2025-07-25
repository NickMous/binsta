<?php

namespace NickMous\Binsta\Twig;

use InvalidArgumentException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

class TwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', [$this, 'path']),
            new TwigFunction('is_on', [$this, 'isOn']),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('is_on', [$this, 'isOn']),
        ];
    }

    /**
     * @param string       $routeName
     * @param array<mixed> $parameters
     * @return string
     */
    public function path(string $routeName, array $parameters = []): string
    {
        return path($routeName, $parameters);
    }

    /**
     * Check if the current route matches the given route name and parameters.
     *
     * @param string $routeName The name of the route to check.
     * @param array<mixed>|null $parameters Optional parameters for the route.
     * @return bool True if the current route matches, false otherwise.
     */
    public function isOn(string $routeName, ?array $parameters = []): bool
    {
        $currentUrl = $_SERVER['REQUEST_URI'];
        $routeUrl = $this->path($routeName, $parameters);

        // Support wildcard matching for routeName ending with .*
        if (str_ends_with($routeName, '.*')) {
            $prefix = rtrim($this->path(substr($routeName, 0, -2) . '.index'), '/');
            $currentUrl = rtrim($currentUrl, '/');
            return str_starts_with($currentUrl, $prefix);
        }

        // Normalize URLs for comparison
        $currentUrl = rtrim($currentUrl, '/');
        $routeUrl = rtrim($routeUrl, '/');

        return $currentUrl === $routeUrl;
    }
}
