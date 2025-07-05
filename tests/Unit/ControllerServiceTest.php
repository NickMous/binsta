<?php

use NickMous\Binsta\Internals\Exceptions\InvalidRouteFileGiven;
use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Exceptions\Route\InvalidRouteClassException;
use NickMous\Binsta\Internals\Exceptions\Route\NoObjectException;
use NickMous\Binsta\Internals\Routes\AbstractRoute;
use NickMous\Binsta\Internals\Services\ControllerService;

covers(ControllerService::class);

it('loads all available routes on initialization', function (AbstractRoute $route) {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');
    $reflection = new ReflectionClass($controllerService);
    $property = $reflection->getProperty('routes');
    $property->setAccessible(true);
    $routes = $property->getValue($controllerService);
    expect($routes)->toBeArray()
        ->toHaveKey($route->path)
        ->and($routes[$route->path])->toBeInstanceOf(AbstractRoute::class);
})->with('valid-routes');

it('throws an exception when the route file does not exist', function () {
    new ControllerService(__DIR__ . '/../Datasets/non-existent-routes.php');
})->throws(InvalidRouteFileGiven::class, 'Invalid route file given:');

it('throws an exception when the route file is empty', function () {
    new ControllerService(__DIR__ . '/../Datasets/empty-routes.php');
})->throws(InvalidRouteFileGiven::class, 'Invalid route file given: The given route file is empty:');

it('throws an exception when a route is not an object', function () {
    new ControllerService(__DIR__ . '/../Datasets/invalid-route-object.php');
})->throws(NoObjectException::class, 'Something different than an object was passed as a route.');

it('throws an exception when a route is not a subclass of AbstractRoute', function () {
    new ControllerService(__DIR__ . '/../Datasets/invalid-route-class.php');
})->throws(InvalidRouteClassException::class, 'Invalid route class:');

it('calls the correct route based on the path', function (AbstractRoute $route) {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');

    ob_start();
    $controllerService->callRoute($route->path);
    $output = ob_get_clean();

    expect($output)->toBeString()
        ->and($output)->toContain('This is the response for the route: ' . $route->path);
})->with('valid-routes');

it('throws an exception when the route does not exist', function () {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');

    ob_start();
    $controllerService->callRoute('/non-existent-route');
    $output = ob_get_clean();

    expect($output)->toBeString()
        ->and($output)->toContain('Route not found: /non-existent-route');
});

it('throws an exception when the route handler does not return a Response', function () {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/invalid-response-route.php');
    $controllerService->callRoute('/invalid-response');
})->throws(InvalidResponseException::class, 'Invalid response provided.');

it('applies given headers to the response', function () {
    $controllerService = new ControllerService(__DIR__ . '/../Datasets/valid-routes.php');

    ob_start();
    $controllerService->callRoute('/with-headers');
    $output = ob_get_clean();

    expect($output)->toBeString()
        ->and($output)->toContain('This is the response for the route: /with-headers')
        ->and(xdebug_get_headers()[0])->toContain('Content-type: text/plain');
});
