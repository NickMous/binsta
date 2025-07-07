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
            throw new RuntimeException('Database configuration is not set in environment variables.');
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
