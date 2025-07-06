<?php

use Nickmous\Binsta\Twig\TwigExtension;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

const VITE_HOST = 'http://localhost:5173';
const DOCKER_HOST = 'host.docker.internal';

if (!function_exists('twig')) {
    /**
     * @return Environment
     */
    function twig(): Environment
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new FilesystemLoader(__DIR__ . '/views');
            $twig = new Environment($loader, [
                'cache' => __DIR__ . '/cache',
                'auto_reload' => true,
            ]);
            $twig->addExtension(new DebugExtension());
            $twig->addExtension(new TwigExtension());
            $twig->addGlobal('username', $_SESSION['username'] ?? null);
            $twig->addGlobal('user_email', $_SESSION['user_email'] ?? null);
            $twig->addGlobal('logged_in', isset($_SESSION['user_id']));
        }

        return $twig;
    }
}

if (!function_exists('path')) {
    /**
     * Generates a URL path based on the controller and method names.
     *
     * @param string $routeName The route name in the format "controller.method".
     * @param array $parameters Optional parameters to include in the query string.
     * @return string The generated URL path.
     */
    function path(string $routeName, array $parameters = []): string
    {
        $routeParts = explode('.', $routeName);

        if (count($routeParts) !== 2) {
            throw new InvalidArgumentException('Route name must be in the format "controller.method"');
        }

        $controller = strtolower($routeParts[0]);
        $method = $routeParts[1];
        $url = '/' . $controller;
        if ($method !== 'index') {
            $url .= '/' . $method;
        }

        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }
}
