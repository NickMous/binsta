<?php

use Dotenv\Dotenv;
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

$kitchens = [
    [
        'id' => 1,
        'name' => 'French kitchen',
        'description' => 'The French kitchen is an internationally acclaimed cuisine with a long tradition. This 
            kitchen is characterized by a great diversity, as is also seen in the Chinese 
            kitchen and Indian kitchen.',
    ],
    [
        'id' => 2,
        'name' => 'Chinese kitchen',
        'description' => 'The Chinese kitchen is the culinary tradition of China and the Chinese people living in the diaspora, 
            mainly in Southeast Asia. Due to the size of China and the presence of many peoples with 
            their own cultures, as well as climatic dependencies and regional food sources, the variations are large.',
    ],
    [
        'id' => 3,
        'name' => 'Dutch kitchen',
        'description' => 'The Dutch kitchen is particularly inspired by the agricultural history of the Netherlands. 
            Although the cuisine can vary by region and there are regional specialties, there are dishes 
            considered typical for the Netherlands. Dutch dishes are often relatively simple and nutritious, 
            such as porridge, Gouda cheese, pancakes, split pea soup, and stamppot.',
    ],
    [
        'id' => 4,
        'name' => 'Mediterranean',
        'description' => 'The Mediterranean kitchen is the cuisine of the Mediterranean region and consists of 
            among others the dozens of different cuisines from Morocco, Tunisia, Spain, Italy, Albania and Greece 
            and part of the south of France (such as the Provençal kitchen and the kitchen of Roussillon).',
    ],
];

$recipes = [
    [
        'id' => 1,
        'name' => 'Pancakes',
        'type' => 'dinner',
        'level' => 'easy',
        'kitchen_id' => 3,
    ],
    [
        'id' => 24,
        'name' => 'Grilled Cheese Sandwich',
        'type' => 'lunch',
        'level' => 'easy',
        'kitchen_id' => 3,
    ],
    [
        'id' => 36,
        'name' => 'Farmers Omelette',
        'type' => 'lunch',
        'level' => 'easy',
        'kitchen_id' => 3,
    ],
    [
        'id' => 47,
        'name' => 'Pulled Pork Sandwich',
        'type' => 'lunch',
        'level' => 'hard',
        'kitchen_id' => 1,
    ],
    [
        'id' => 5,
        'name' => 'Mashed Potatoes with Braised Beef',
        'type' => 'dinner',
        'level' => 'medium',
        'kitchen_id' => 4,
    ],
    [
        'id' => 6,
        'name' => 'Nasi Goreng with Babi Ketjap',
        'type' => 'dinner',
        'level' => 'hard',
        'kitchen_id' => 2,
    ],
];

$users = [
    [
        'username' => 'sander',
        'email' => 'sander@nickmous.com',
        'password' => password_hash('password', PASSWORD_BCRYPT),
    ],
];

foreach ($kitchens as $kitchenData) {
    $kitchen = R::dispense('kitchen');
    $kitchen->name = $kitchenData['name'];
    $kitchen->description = $kitchenData['description'];
    $id = R::store($kitchen);
    echo "Kitchen with ID $id created: {$kitchenData['name']}\n";
}

foreach ($recipes as $recipeData) {
    $recipe = R::dispense('recipe');
    $recipe->oldId = $recipeData['id'];
    $recipe->name = $recipeData['name'];
    $recipe->type = $recipeData['type'];
    $recipe->level = $recipeData['level'];
    $recipe->kitchen = R::load('kitchen', $recipeData['kitchen_id']);
    $id = R::store($recipe);
    echo "Recipe with ID $id created: {$recipeData['name']}\n";
}

foreach ($users as $userData) {
    $user = R::dispense('user');
    $user->username = $userData['username'];
    $user->email = $userData['email'];
    $user->password = $userData['password'];
    $id = R::store($user);
    echo "User with ID $id created: {$userData['email']}\n";
}
