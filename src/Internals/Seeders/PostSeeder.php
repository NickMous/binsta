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

        // Create some forked posts to demonstrate fork functionality
        // Get some existing posts to fork from
        $posts = \RedBeanPHP\R::findAll('post', 'ORDER BY RAND() LIMIT 10');
        $postIds = array_keys($posts);

        if (!empty($postIds)) {
            // Create 5-8 forks from random existing posts
            $forkCount = rand(5, 8);
            for ($i = 0; $i < $forkCount; $i++) {
                $originalPostId = $postIds[array_rand($postIds)];
                $userId = rand(1, 15); // Different users forking posts

                PostFactory::new()
                    ->forkedFrom((int)$originalPostId)
                    ->withUser($userId)
                    ->create();
            }
        }

        echo "Created posts with factory system (including forked posts)\n";
    }
}
