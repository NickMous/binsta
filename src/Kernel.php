<?php

namespace NickMous\Binsta;

use Dotenv\Dotenv;
use NickMous\Binsta\Managers\DatabaseManager;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

use function Sentry\init;

class Kernel
{
    public function init(): void
    {
        // Initialize the framework components
        $this->registerAutoloaders();
        $this->initializeSentry();
        $this->initializeWhoops();
        $this->loadEnvironmentVariables();
        $this->initializeDatabase();
        $this->initializeSession();
    }

    private function loadEnvironmentVariables(): void
    {
        $baseDir = __DIR__ . '/..';

        // Determine which environment file to load
        $envFile = $this->determineEnvironmentFile($baseDir);

        if (file_exists($envFile)) {
            $dotenv = Dotenv::createImmutable($baseDir, basename($envFile));
            $dotenv->load();
        }
    }

    /**
     * Determine which environment file to load based on context
     */
    private function determineEnvironmentFile(string $baseDir): string
    {
        // Debug information for CI troubleshooting
        if (!empty($_ENV['CI']) || !empty($_SERVER['CI'])) {
            error_log("CI Environment detected");
            error_log("GITHUB_ACTIONS env: " . ($_ENV['GITHUB_ACTIONS'] ?? 'not set'));
            error_log("CI env: " . ($_ENV['CI'] ?? 'not set'));
            error_log("GITHUB_ACTIONS server: " . ($_SERVER['GITHUB_ACTIONS'] ?? 'not set'));
            error_log("CI server: " . ($_SERVER['CI'] ?? 'not set'));
            error_log("isGitHubCI(): " . ($this->isGitHubCI() ? 'true' : 'false'));
            error_log(".env.ci exists: " . (file_exists("$baseDir/.env.ci") ? 'true' : 'false'));
        }

        // 1. Explicit environment variable override
        if (!empty($_ENV['ENV_FILE'])) {
            $envFile = "$baseDir/{$_ENV['ENV_FILE']}";
            if (file_exists($envFile)) {
                return $envFile;
            }
        }

        // 2. Environment-specific detection (check context first, then file existence)
        if ($this->isGitHubCI() && file_exists("$baseDir/.env.ci")) {
            error_log("Using .env.ci file");
            return "$baseDir/.env.ci";
        }

        if ($this->isDDEV() && file_exists("$baseDir/.env.ddev")) {
            return "$baseDir/.env.ddev";
        }

        if ($this->isTesting() && file_exists("$baseDir/.env.testing")) {
            return "$baseDir/.env.testing";
        }

        // 3. Fallback to local development files
        if (file_exists("$baseDir/.env.local")) {
            return "$baseDir/.env.local";
        }

        // 4. Default fallback
        if (file_exists("$baseDir/.env")) {
            error_log("Using default .env file");
            return "$baseDir/.env";
        }

        // Return default even if it doesn't exist
        error_log("No .env file found, returning default path");
        return "$baseDir/.env";
    }

    /**
     * Check if running in GitHub CI/CD
     */
    private function isGitHubCI(): bool
    {
        return !empty($_ENV['GITHUB_ACTIONS']) ||
               !empty($_ENV['CI']) ||
               !empty($_SERVER['GITHUB_ACTIONS']) ||
               !empty($_SERVER['CI']);
    }

    /**
     * Check if running in DDEV environment
     */
    private function isDDEV(): bool
    {
        return !empty($_ENV['DDEV_PROJECT']) ||
               !empty($_ENV['DDEV_SITENAME']) ||
               file_exists('/.ddev-container');
    }

    /**
     * Check if running in testing environment
     */
    private function isTesting(): bool
    {
        return (!empty($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing') ||
               !empty($_ENV['TESTING']) ||
               defined('PEST_VERSION') ||
               defined('PHPUNIT_COMPOSER_INSTALL');
    }

    private function registerAutoloaders(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    private function initializeWhoops(): void
    {
        if ($this->isTesting()) {
            return;
        }

        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->register();
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
