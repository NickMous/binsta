<?php

use Dotenv\Dotenv;
use NickMous\Binsta\Entities\User;
use RedBeanPHP\R;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$databaseType = $_ENV['DB_CONNECTION'];
$databaseName = $_ENV['DB_DATABASE'];
$databaseHost = $_ENV['DB_HOST'];
$databasePort = $_ENV['DB_PORT'];
$databaseUser = $_ENV['DB_USERNAME'];
$databasePassword = $_ENV['DB_PASSWORD'];

if (!$databaseType || !$databaseName || !$databaseHost || !$databaseUser || !$databasePassword) {
    die("Database configuration is not set in environment variables.\n");
}

R::setup(
    "$databaseType:host=$databaseHost;port=$databasePort;dbname=$databaseName",
    $databaseUser,
    $databasePassword
);

if (!R::testConnection()) {
    die("Failed to connect to the database.\n");
}

// empty the database
R::nuke();

// Create a couple of users
$user1 = new User();
$user1->name = 'John Doe';
$user1->username = 'johndoe';
$user1->email = 'johndoe@example.com';
$user1->password = 'password123';
$user1->createdAt = new DateTime();
$user1->updatedAt = new DateTime();
$user1->save();

$user2 = new User();
$user2->name = 'Jane Smith';
$user2->username = 'janesmith';
$user2->email = 'janesmith@example.com';
$user2->password = 'password456';
$user2->createdAt = new DateTime();
$user2->updatedAt = new DateTime();
$user2->save();

$user3 = new User();
$user3->name = 'Alice Johnson';
$user3->username = 'alicejohnson';
$user3->email = 'alicejohnson@example.com';
$user3->password = 'password789';
$user3->createdAt = new DateTime();
$user3->updatedAt = new DateTime();
$user3->save();

echo "Database seeded with initial users.\n";
