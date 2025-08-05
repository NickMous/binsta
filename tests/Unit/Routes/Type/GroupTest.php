<?php

use NickMous\Binsta\Internals\Routes\Type\Group;
use NickMous\Binsta\Internals\Routes\Type\Get;
use NickMous\Binsta\Internals\Routes\Type\Post;
use NickMous\Binsta\Internals\Response\JsonResponse;

covers(Group::class);

describe('Group', function (): void {
    test('creates group with path and empty routes', function (): void {
        $group = new Group('/api');

        expect($group->path)->toBe('/api');
        expect($group->routes)->toBe([]);
    });

    test('creates group with path and routes array', function (): void {
        $getRoute = new Get('/users', fn() => new JsonResponse(['users' => []]));
        $postRoute = new Post('/users', fn() => new JsonResponse(['created' => true]));

        $group = new Group('/api', [$getRoute, $postRoute]);

        expect($group->path)->toBe('/api');
        expect($group->routes)->toHaveCount(2);
        expect($group->routes[0])->toBe($getRoute);
        expect($group->routes[1])->toBe($postRoute);
    });

    test('creates nested groups', function (): void {
        $userRoutes = new Group('/users', [
            new Get('/', fn() => new JsonResponse(['users' => []])),
            new Post('/', fn() => new JsonResponse(['created' => true])),
        ]);

        $apiGroup = new Group('/api', [$userRoutes]);

        expect($apiGroup->path)->toBe('/api');
        expect($apiGroup->routes)->toHaveCount(1);
        expect($apiGroup->routes[0])->toBe($userRoutes);
        expect($userRoutes->routes)->toHaveCount(2);
    });

    test('group properties are public and accessible', function (): void {
        $routes = [new Get('/test', fn() => new JsonResponse(['test' => true]))];
        $group = new Group('/prefix', $routes);

        // Test that properties are public and directly accessible
        expect($group->path)->toBe('/prefix');
        expect($group->routes)->toBe($routes);

        // Test that we can modify them directly (since they're public)
        $group->path = '/modified';
        expect($group->path)->toBe('/modified');

        $newRoute = new Post('/new', fn() => new JsonResponse(['new' => true]));
        $group->routes[] = $newRoute;
        expect($group->routes)->toHaveCount(2);
        expect($group->routes[1])->toBe($newRoute);
    });
});
