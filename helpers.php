<?php

use JetBrains\PhpStorm\NoReturn;
use Nickmous\MyOwnFramework\Twig\TwigExtension;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;

if (!function_exists('displayPage')) {
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    function displayPage(string|TemplateWrapper $template, array $with = []): void
    {
        $twig = twig();
        $template = $twig->load($template);
        $template->display($with);
    }
}

if (!function_exists('error')) {
    /**
     * @param int $errorNumber
     * @param string $errorMessage
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[NoReturn] function error(int $errorNumber, string $errorMessage): void
    {
        http_response_code($errorNumber);
        $twig = twig();
        $template = $twig->load('error.twig');
        $template->display(['errorNumber' => $errorNumber, 'errorMessage' => $errorMessage]);

        if (getenv('APP_ENV') === 'testing') {
            // In testing environment, we don't want to exit the script
            return;
        }

        exit;
    }
}

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

if (!function_exists('redirect')) {
    /**
     * Redirects to a given URL.
     *
     * @param string $url The URL to redirect to.
     * @throws Exception If the URL is empty.
     */
    #[NoReturn] function redirect(string $url): void
    {
        if (empty($url)) {
            throw new Exception('Redirect URL cannot be empty');
        }

        header('Location: ' . $url);
        exit;
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
