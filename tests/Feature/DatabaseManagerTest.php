<?php

use NickMous\MyOwnFramework\Managers\DatabaseManager;

covers(DatabaseManager::class);

it('instantiates the connection', function (): void {
    DatabaseManager::instantiate();
    expect(\RedBeanPHP\R::hasDatabase('default'))->toBeTrue();
});

it('throws an error when database configuration is missing', function (): void {
    $_ENV['DB_CONNECTION'] = '';
    $_ENV['DB_DATABASE'] = '';
    $_ENV['DB_HOST'] = '';
    $_ENV['DB_PORT'] = '';
    $_ENV['DB_USERNAME'] = '';
    $_ENV['DB_PASSWORD'] = '';

    expect(function (): void {
        DatabaseManager::instantiate();
    })->toThrow(RuntimeException::class, 'Database configuration is not set in environment variables.');
});
