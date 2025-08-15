<?php

use NickMous\Binsta\Internals\Seeders\DatabaseSeeder;
use NickMous\Binsta\Kernel;
use RedBeanPHP\R;

require_once __DIR__ . '/vendor/autoload.php';

// Initialize the application using Kernel
$kernel = new Kernel();
$kernel->init();

echo "Application initialized using Kernel.\n";

// Check database connection
if (!R::testConnection()) {
    die("Failed to connect to the database. Check your environment configuration.\n");
}

echo "Database connection established.\n";

// Clear the database
echo "Clearing database...\n";
R::nuke();
echo "Database cleared.\n\n";

// Run seeders using factory system
echo "Running database seeders...\n";
$seeder = new DatabaseSeeder();
$seeder->run();

echo "\nDatabase seeding completed successfully!\n";
