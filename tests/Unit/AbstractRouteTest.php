<?php

use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\AbstractRoute;
use NickMous\Binsta\Internals\Routes\Route;
use NickMous\Binsta\Internals\Routes\Type\Get;
use NickMous\Binsta\Internals\Routes\Type\Group;

covers(AbstractRoute::class, Route::class, Get::class, Group::class);

it('can execute the given closure', function (): void {
    $response = new Response("Hello, World!");

    $route = new class ('/test', function () use ($response) {
        return $response;
    }, 'GET') extends AbstractRoute {
    };

    $returnedResponse = $route->handle();

    expect($returnedResponse)->toBe($response);
});

it('throws an exception when the closure does not return a Response', function (): void {
    $route = new class ('/test', function () {
        return "Not a response";
    }, 'GET') extends AbstractRoute {
    };

    $route->handle();
})->throws(InvalidResponseException::class, 'Invalid response provided.');

it('defines the get method without specifying the method', function (): void {
    $route = Route::get('/test', function () {
        return new Response("GET request handled");
    });

    expect($route)->toBeInstanceOf(Get::class);
});

it('returns a group route when using the group method', function (): void {
    $groupRoute = Route::group('/api', [
        Route::get('/users', function () {
            return new Response("Users list");
        }),
        Route::get('/posts', function () {
            return new Response("Posts list");
        }),
    ]);

    expect($groupRoute)->toBeInstanceOf(Group::class)
        ->and($groupRoute->path)->toBe('/api')
        ->and($groupRoute->routes)->toHaveCount(2);
});

it('handles class method route execution', function (): void {
    // Create a test controller class
    $controller = new class {
        public function testMethod(): Response
        {
            return new Response("Class method response");
        }
    };

    $route = new class ('/test', null, $controller::class, 'testMethod') extends AbstractRoute {
    };

    $response = $route->handle();

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->content)->toBe("Class method response");
});

it('throws exception when class name is not set for class method route', function (): void {
    $route = new class ('/test', null, null, 'testMethod') extends AbstractRoute {
    };

    $route->handle();
})->throws(RuntimeException::class, 'Class name or method name is not set for this route.');

it('throws exception when method name is not set for class method route', function (): void {
    $route = new class ('/test', null, 'TestClass', null) extends AbstractRoute {
    };

    $route->handle();
})->throws(RuntimeException::class, 'Class name or method name is not set for this route.');

it('throws exception when method does not exist in class', function (): void {
    $controller = new class {
        public function existingMethod(): Response
        {
            return new Response("Existing method");
        }
    };

    $route = new class ('/test', null, $controller::class, 'nonExistentMethod') extends AbstractRoute {
    };

    $route->handle();
})->throws(RuntimeException::class, 'Method nonExistentMethod does not exist in class');

it('throws exception when class method does not return Response', function (): void {
    $controller = new class {
        public function invalidMethod(): string
        {
            return "Not a response";
        }
    };

    $route = new class ('/test', null, $controller::class, 'invalidMethod') extends AbstractRoute {
    };

    $route->handle();
})->throws(InvalidResponseException::class, 'Invalid response provided.');
