<?php

use NickMous\Binsta\Internals\Exceptions\InvalidRouteFileGiven;
use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Exceptions\Route\InvalidRouteClassException;
use NickMous\Binsta\Internals\Exceptions\Route\NoObjectException;
use NickMous\Binsta\Internals\Routes\AbstractRoute;
use NickMous\Binsta\Internals\Services\ControllerService;

covers(ControllerService::class, AbstractRoute::class);

it('loads all available routes on initialization', function (AbstractRoute $route): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');
    $reflection = new ReflectionClass($controllerService);
    $property = $reflection->getProperty('routes');
    $property->setAccessible(true);
    $routes = $property->getValue($controllerService);
    expect($routes)->toBeArray()
        ->toHaveKey($route->method)
        ->and($routes[$route->method])->toHaveKey($route->path)
        ->and($routes[$route->method][$route->path])->toBeInstanceOf(AbstractRoute::class);
})->with('valid-routes');

it('loads routes from multiple files', function (): void {
    $controllerService = new ControllerService(
        __DIR__ . '/../Datasets/valid-routes.php',
        __DIR__ . '/../Datasets/api-routes.php'
    );
    $reflection = new ReflectionClass($controllerService);
    $property = $reflection->getProperty('routes');
    $property->setAccessible(true);
    $routes = $property->getValue($controllerService);

    expect($routes)->toBeArray()
        ->toHaveKey('GET')
        ->and($routes['GET'])->toHaveKey('/')
        ->and($routes['GET'])->toHaveKey('/api/test');
});

it('throws an exception when the route file does not exist', function (): void {
    new ControllerService(__DIR__ . '/../Datasets/non-existent-routes.php');
})->throws(InvalidRouteFileGiven::class, 'Invalid route file given:');

it('throws an exception when the route file is empty', function (): void {
    new ControllerService(__DIR__ . '/../Datasets/empty-routes.php');
})->throws(InvalidRouteFileGiven::class, 'Invalid route file given: The given route file is empty:');

it('throws an exception when a route is not an object', function (): void {
    new ControllerService(__DIR__ . '/../Datasets/invalid-route-object.php');
})->throws(NoObjectException::class, 'Something different than an object was passed as a route.');

it('throws an exception when a route is not a subclass of AbstractRoute', function (): void {
    new ControllerService(__DIR__ . '/../Datasets/invalid-route-class.php');
})->throws(InvalidRouteClassException::class, 'Invalid route class:');

it('calls the correct route based on the path', function (AbstractRoute $route): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');

    ob_start();
    $controllerService->callRoute($route->path, $route->method);
    $output = ob_get_clean();

    expect($output)->toBeString()
        ->and($output)->toContain('This is the response for the route: ' . $route->path);
})->with('valid-routes');

it('throws an exception when the route does not exist', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');

    ob_start();
    $controllerService->callRoute('/non-existent-route', 'GET');
    $output = ob_get_clean();

    expect($output)->toBeString()
        ->and($output)->toContain('Route not found: GET: /non-existent-route');
});

it('throws an exception when the route handler does not return a Response', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/invalid-response-route.php');
    $controllerService->callRoute('/invalid-response', 'GET');
})->throws(InvalidResponseException::class, 'Invalid response provided.');

it('applies given headers to the response', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');

    ob_start();
    $controllerService->callRoute('/with-headers', 'GET');
    $output = ob_get_clean();

    $headers = xdebug_get_headers();
    $contentTypeHeader = null;
    foreach ($headers as $header) {
        if (str_starts_with($header, 'Content-type:')) {
            $contentTypeHeader = $header;
            break;
        }
    }

    expect($output)->toBeString()
        ->and($output)->toContain('This is the response for the route: /with-headers')
        ->and($contentTypeHeader)->toStartWith('Content-type: text/plain');
});

it('renders a vue response correctly', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');

    ob_start();
    $controllerService->callRoute('/vue-response', 'GET');
    $output = ob_get_clean();

    expect($output)->toBeString()
        ->and($output)->toContain('<hello-world></hello-world>');
});

it('throws an exception when the content is empty', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/empty-content-route.php');
    $controllerService->callRoute('/empty-content', 'GET');
})->throws(InvalidResponseException::class, 'The response content is empty.');

it('matches routes with numeric parameters', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/api-routes.php');

    ob_start();
    $controllerService->callRoute('/api/users/123', 'GET');
    $output = ob_get_clean();

    expect($output)->toContain('User ID: 123');
});

it('does not match routes with invalid numeric parameters', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/api-routes.php');

    ob_start();
    $controllerService->callRoute('/api/users/abc', 'GET');
    $output = ob_get_clean();

    expect($output)->toContain('Route not found: GET: /api/users/abc');
});

it('matches routes with string pattern parameters', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/api-routes.php');

    ob_start();
    $controllerService->callRoute('/api/posts/my-blog-post', 'GET');
    $output = ob_get_clean();

    expect($output)->toContain('Post slug: my-blog-post');
});

it('does not match routes with invalid string pattern parameters', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/api-routes.php');

    ob_start();
    $controllerService->callRoute('/api/posts/My-Blog-Post', 'GET');
    $output = ob_get_clean();

    expect($output)->toContain('Route not found: GET: /api/posts/My-Blog-Post');
});

it('matches catch-all routes', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/api-routes.php');

    ob_start();
    $controllerService->callRoute('/api/catch/anything/goes/here', 'GET');
    $output = ob_get_clean();

    expect($output)->toContain('Catch-all path: anything/goes/here');
});

it('prioritizes exact matches over pattern matches', function (): void {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/api-routes.php');

    ob_start();
    $controllerService->callRoute('/api/test', 'GET');
    $output = ob_get_clean();

    expect($output)->toContain('API test endpoint');
});

it('processes route files in order (last file wins for duplicate paths)', function (): void {
    // Create a temporary route file that conflicts with api-routes.php
    $conflictRoutes = __DIR__ . '/../Datasets/conflict-routes.php';
    file_put_contents($conflictRoutes, '<?php
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::get("/api/test", function () {
        return new Response("Conflict test endpoint");
    }),
];');

    $controllerService = new ControllerService(
        $conflictRoutes,
        __DIR__ . '/../Datasets/api-routes.php'
    );

    ob_start();
    $controllerService->callRoute('/api/test', 'GET');
    $output = ob_get_clean();

    // Last file loaded should win (api-routes.php overwrites conflict-routes.php)
    expect($output)->toContain('API test endpoint');

    // Clean up
    unlink($conflictRoutes);
});

it('extracts multiple parameters correctly', function (): void {
    // Create a temporary route file with multiple parameters
    $multiParamRoutes = __DIR__ . '/../Datasets/multi-param-routes.php';
    file_put_contents($multiParamRoutes, '<?php
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\Route;

return [
    Route::get("/api/users/{id:\d+}/posts/{slug:[a-z-]+}", function () {
        $params = $GLOBALS["route_parameters"] ?? [];
        return new Response("User: " . ($params["id"] ?? "unknown") . ", Post: " . ($params["slug"] ?? "unknown"));
    }),
];');

    $controllerService = new ControllerService($multiParamRoutes);

    ob_start();
    $controllerService->callRoute('/api/users/456/posts/my-awesome-post', 'GET');
    $output = ob_get_clean();

    expect($output)->toContain('User: 456, Post: my-awesome-post');

    // Clean up
    unlink($multiParamRoutes);
});

it('loads group routes correctly', function (): void {
    // Test for lines 72-73 coverage: Group route handling
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/group-routes.php');

    $reflection = new ReflectionClass($controllerService);
    $property = $reflection->getProperty('routes');
    $property->setAccessible(true);
    $routes = $property->getValue($controllerService);

    // Check that group routes are loaded
    expect($routes)->toBeArray()
        ->toHaveKey('GET')
        ->and($routes['GET'])->toHaveKey('/api/users')
        ->and($routes['GET'])->toHaveKey('/api/posts')
        ->and($routes['GET'])->toHaveKey('/standalone');
});

it('handles RouteNotFound for missing HTTP method', function (): void {
    // Test for lines 93-94 coverage: RouteNotFound when method doesn't exist
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');

    ob_start();
    $controllerService->callRoute('/', 'DELETE'); // DELETE method doesn't exist
    $output = ob_get_clean();

    expect($output)->toContain('Route not found: DELETE: /');
});
