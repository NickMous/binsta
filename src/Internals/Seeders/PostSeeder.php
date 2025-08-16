<?php

namespace NickMous\Binsta\Internals\Seeders;

use NickMous\Binsta\Internals\Factories\PostFactory;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Create a variety of posts
        PostFactory::new()
            ->count(25)
            ->create();

        // Create some recent posts
        PostFactory::new()
            ->recent()
            ->count(8)
            ->create();

        // Create some posts for specific languages
        $popularLanguages = ['javascript', 'php', 'python', 'typescript', 'java'];
        foreach ($popularLanguages as $language) {
            PostFactory::new()
                ->withLanguage($language)
                ->count(3)
                ->create();
        }

        echo "Created posts with factory system\n";
    }
}
