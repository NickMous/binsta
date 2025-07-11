<?php

use NickMous\Binsta\Internals\Response\Errors\Http\Route\RouteNotFound;

covers(RouteNotFound::class);

describe('RouteNotFound', function () {
    test('creates RouteNotFound error with route name and method', function () {
        $error = new RouteNotFound('/api/users', 'GET');

        expect($error->content)->toBe('Route not found: GET: /api/users');
        expect($error->statusCode)->toBe(404);
    });

    test('creates RouteNotFound error with different route and method', function () {
        $error = new RouteNotFound('/admin/dashboard', 'POST');

        expect($error->content)->toBe('Route not found: POST: /admin/dashboard');
        expect($error->statusCode)->toBe(404);
    });

    test('creates RouteNotFound error with empty route name', function () {
        $error = new RouteNotFound('', 'DELETE');

        expect($error->content)->toBe('Route not found: DELETE: ');
        expect($error->statusCode)->toBe(404);
    });
});
