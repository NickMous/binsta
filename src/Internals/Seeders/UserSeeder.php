<?php

namespace NickMous\Binsta\Internals\Seeders;

use NickMous\Binsta\Internals\Factories\UserFactory;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create regular users
        UserFactory::new()
            ->count(10)
            ->create();

        // Create some users without profiles
        UserFactory::new()
            ->withoutBiography()
            ->withoutProfilePicture()
            ->count(5)
            ->create();

        echo "Created users with factory system\n";
    }
}
