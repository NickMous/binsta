<?php

namespace Nickmous\MyOwnFramework;

use Dotenv\Dotenv;
use Nickmous\MyOwnFramework\Managers\DatabaseManager;
use Spatie\Ignition\Ignition;

class Kernel
{
    public function __construct()
    {
        // Initialize the framework components
        $this->loadEnvironmentVariables();
        $this->registerAutoloaders();
        $this->initializeIgnition();
        $this->initializeDatabase();
        $this->initializeSession();
    }

    private function loadEnvironmentVariables(): void
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/01-binsta/');
            $dotenv->load();
        }
    }

    private function registerAutoloaders(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    private function initializeIgnition(): void
    {
        if (class_exists(Ignition::class)) {
            Ignition::make()
                ->applicationPath(__DIR__ . '/01-binsta/')
                ->register();
        }
    }

    private function initializeDatabase(): void
    {
        DatabaseManager::instantiate();
    }

    private function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
