<?php

namespace NickMous\Binsta\Internals\Seeders;

use NickMous\Binsta\Internals\Factories\CommentFactory;
use RedBeanPHP\R;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing post IDs from the database
        $postIds = R::getCol('SELECT id FROM post ORDER BY id');

        if (empty($postIds)) {
            echo "No posts found. Please run PostSeeder first.\n";
            return;
        }

        // Create comments for each post
        foreach ($postIds as $postId) {
            // Random number of comments per post (0-8 comments)
            $commentCount = rand(0, 8);

            if ($commentCount > 0) {
                CommentFactory::new()
                    ->withPost((int)$postId)
                    ->count($commentCount)
                    ->create();
            }
        }

        // Create some recent comments on popular posts (first 10 posts)
        $popularPostIds = array_slice($postIds, 0, min(10, count($postIds)));
        foreach ($popularPostIds as $postId) {
            // Add 2-4 recent comments
            CommentFactory::new()
                ->withPost((int)$postId)
                ->recent()
                ->count(rand(2, 4))
                ->create();
        }

        // Create some positive comments
        $randomPostIds = array_rand(array_flip($postIds), min(15, count($postIds)));
        if (!is_array($randomPostIds)) {
            $randomPostIds = [$randomPostIds];
        }
        foreach ($randomPostIds as $postId) {
            CommentFactory::new()
                ->withPost((int)$postId)
                ->positive()
                ->count(1)
                ->create();
        }

        // Create some question comments
        $questionPostIds = array_rand(array_flip($postIds), min(10, count($postIds)));
        if (!is_array($questionPostIds)) {
            $questionPostIds = [$questionPostIds];
        }
        foreach ($questionPostIds as $postId) {
            CommentFactory::new()
                ->withPost((int)$postId)
                ->question()
                ->count(1)
                ->create();
        }

        echo "Created comments with factory system\n";
    }
}
