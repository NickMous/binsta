<?php

namespace NickMous\MyOwnFramework;

use Dotenv\Dotenv;
use NickMous\MyOwnFramework\Managers\DatabaseManager;
use Spatie\Ignition\Ignition;

class Kernel
{
    public function init(): void
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
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
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

    public function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
