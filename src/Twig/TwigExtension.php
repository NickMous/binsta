<?php

namespace Nickmous\MyOwnFramework\Twig;

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

    public function path(string $routeName, array $parameters = []): string
    {
        return path($routeName, $parameters);
    }

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