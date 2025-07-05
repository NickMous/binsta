<?php

use Nickmous\Binsta\Twig\TwigExtension;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

const VITE_HOST = 'http://localhost:5133';

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

if (!function_exists('vite')) {
    /**
     * Generates HTML tags for Vite assets.
     *
     * @param string $entry
     * @return string
     */
    function vite(string $entry): string
    {
        return "\n" . jsTag($entry)
            . "\n" . jsPreloadImports($entry)
            . "\n" . cssTag($entry);
    }
}

if (!function_exists('isDev')) {
    function isDev(string $entry): bool
    {
        // This method is very useful for the local server
        // if we try to access it, and by any means, didn't started Vite yet
        // it will fallback to load the production files from manifest
        // so you still navigate your site as you intended!

        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        $handle = curl_init(VITE_HOST . '/' . $entry);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_NOBODY, true);

        curl_exec($handle);
        $error = curl_errno($handle);
        curl_close($handle);

        return $exists = !$error;
    }
}


// Helpers to print tags
if (!function_exists('jsTag')) {
    function jsTag(string $entry): string
    {
        $url = isDev($entry)
            ? VITE_HOST . '/' . $entry
            : assetUrl($entry);

        if (!$url) {
            return '';
        }
        if (isDev($entry)) {
            return '<script type="module" src="' . VITE_HOST . '/@vite/client"></script>' . "\n"
                . '<script type="module" src="' . $url . '"></script>';
        }
        return '<script type="module" src="' . $url . '"></script>';
    }
}

if (!function_exists('jsPreloadImports')) {
    function jsPreloadImports(string $entry): string
    {
        if (isDev($entry)) {
            return '';
        }

        $res = '';
        foreach (importsUrls($entry) as $url) {
            $res .= '<link rel="modulepreload" href="'
                . $url
                . '">';
        }
        return $res;
    }
}

if (!function_exists('cssTag')) {
    function cssTag(string $entry): string
    {
        // not needed on dev, it's inject by Vite
        if (isDev($entry)) {
            return '';
        }

        $tags = '';
        foreach (cssUrls($entry) as $url) {
            $tags .= '<link rel="stylesheet" href="'
                . $url
                . '">';
        }
        return $tags;
    }
}

if (!function_exists('getManifest')) {
    // Helpers to locate files
    function getManifest(): array
    {
        $content = file_get_contents(__DIR__ . '/dist/.vite/manifest.json');
        return json_decode($content, true);
    }
}

if (!function_exists('assetUrl')) {
    function assetUrl(string $entry): string
    {
        $manifest = getManifest();

        return isset($manifest[$entry])
            ? '/dist/' . $manifest[$entry]['file']
            : '';
    }
}

if (!function_exists('importUrls')) {
    function importsUrls(string $entry): array
    {
        $urls = [];
        $manifest = getManifest();

        if (!empty($manifest[$entry]['imports'])) {
            foreach ($manifest[$entry]['imports'] as $imports) {
                $urls[] = '/dist/' . $manifest[$imports]['file'];
            }
        }
        return $urls;
    }
}

if (!function_exists('cssUrls')) {
    function cssUrls(string $entry): array
    {
        $urls = [];
        $manifest = getManifest();

        if (!empty($manifest[$entry]['css'])) {
            foreach ($manifest[$entry]['css'] as $file) {
                $urls[] = '/dist/' . $file;
            }
        }
        return $urls;
    }
}
