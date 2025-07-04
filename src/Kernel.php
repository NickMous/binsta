<?php

namespace Nickmous\MyOwnFramework;

use Dotenv\Dotenv;
use Nickmous\MyOwnFramework\Managers\DatabaseManager;
use Spatie\Ignition\Ignition;

use function Sentry\init;

class Kernel
{
    public function init(): void
    {
        // Initialize the framework components
        $this->registerAutoloaders();
        $this->initializeSentry();
        $this->initializeIgnition();
        $this->loadEnvironmentVariables();
        $this->initializeDatabase();
        $this->initializeSession();
    }

    private function loadEnvironmentVariables(): void
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '../../');
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

    public function initializeSentry(): void
    {
        if (empty($_ENV['SENTRY_DSN'])) {
            return; // Sentry DSN is not set, skip initialization
        }

        init([
            'dsn' => $_ENV['SENTRY_DSN'],
            'traces_sample_rate' => 1.0,
            'profiles_sample_rate' => 1.0,
        ]);
    }
}
