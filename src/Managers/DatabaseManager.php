<?php

namespace NickMous\Binsta\Managers;

use RedBeanPHP\R;
use RuntimeException;

class DatabaseManager
{
    private static bool $initialized = false;

    public static function instantiate(): void
    {
        // Prevent multiple initializations
        if (self::$initialized) {
            return;
        }

        $databaseType = $_ENV['DB_CONNECTION'];
        $databaseName = $_ENV['DB_DATABASE'];
        $databaseHost = $_ENV['DB_HOST'];
        $databasePort = $_ENV['DB_PORT'];
        $databaseUser = $_ENV['DB_USERNAME'];
        $databasePassword = $_ENV['DB_PASSWORD'];

        if (!$databaseType || !$databaseName || !$databaseHost || !$databaseUser || !$databasePassword) {
            // Debug database configuration
            error_log("Database configuration missing:");
            error_log("DB_CONNECTION: " . ($databaseType ?? 'not set'));
            error_log("DB_DATABASE: " . ($databaseName ?? 'not set'));
            error_log("DB_HOST: " . ($databaseHost ?? 'not set'));
            error_log("DB_PORT: " . ($databasePort ?? 'not set'));
            error_log("DB_USERNAME: " . ($databaseUser ?? 'not set'));
            error_log("DB_PASSWORD: " . ($databasePassword ?? 'not set'));
            throw new RuntimeException('Database configuration is not set in environment variables.');
        }

        // Debug successful database configuration
        if (!empty($_ENV['CI']) || !empty($_SERVER['CI'])) {
            error_log("Database connection string: $databaseType:host=$databaseHost;port=$databasePort;dbname=$databaseName");
            error_log("Database user: $databaseUser");
        }

        R::setup(
            "$databaseType:host=$databaseHost;port=$databasePort;dbname=$databaseName",
            $databaseUser,
            $databasePassword
        );

        self::$initialized = true;
    }

    /**
     * Reset the initialization state (useful for testing)
     */
    public static function reset(): void
    {
        self::$initialized = false;
        R::$toolboxes = [];
    }
}
