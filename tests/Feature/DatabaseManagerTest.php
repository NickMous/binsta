<?php

use NickMous\Binsta\Managers\DatabaseManager;
use NickMous\Binsta\Kernel;

covers(DatabaseManager::class);

it('instantiates the connection', function (): void {
    // Initialize environment for database tests
    new Kernel()->init();

    DatabaseManager::instantiate();
    expect(\RedBeanPHP\R::hasDatabase('default'))->toBeTrue();
});

it('throws an error when database configuration is missing', function (): void {
    // Reset the database manager first
    DatabaseManager::reset();

    // Clear environment variables
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
