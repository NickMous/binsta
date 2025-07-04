<?php

namespace NickMous\MyOwnFramework\Managers;

use RedBeanPHP\R;
use RuntimeException;

class DatabaseManager
{
    public static function instantiate(): void
    {
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
    }
}
