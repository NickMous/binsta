<?php

use NickMous\Binsta\Internals\Exceptions\Response\InvalidResponseException;
use NickMous\Binsta\Internals\Response\Response;
use NickMous\Binsta\Internals\Routes\AbstractRoute;
use NickMous\Binsta\Internals\Routes\Route;
use NickMous\Binsta\Internals\Routes\Type\Get;

covers(AbstractRoute::class, Route::class, Get::class);

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
