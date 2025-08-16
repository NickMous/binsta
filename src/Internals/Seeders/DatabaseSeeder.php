<?php

namespace NickMous\Binsta\Internals\Seeders;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            UserFollowSeeder::class,
            PostSeeder::class,
        ]);
    }
}
