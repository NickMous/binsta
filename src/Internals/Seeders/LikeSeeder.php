<?php

namespace NickMous\Binsta\Internals\Seeders;

use NickMous\Binsta\Internals\Factories\LikeFactory;

class LikeSeeder extends Seeder
{
    public function run(): void
    {
        // Create likes using the factory system like other seeders

        // Create recent likes (more activity on recent posts)
        LikeFactory::new()
            ->recent()
            ->count(30)
            ->create();

        // Create some older likes
        LikeFactory::new()
            ->old()
            ->count(15)
            ->create();

        // Create general likes with random distribution
        LikeFactory::new()
            ->count(40)
            ->create();

        // Create some popular posts (specific posts with more likes)
        for ($postId = 1; $postId <= 5; $postId++) {
            LikeFactory::new()
                ->forPost($postId)
                ->count(rand(3, 8))
                ->create();
        }

        // Make sure specific users have some likes
        for ($userId = 1; $userId <= 3; $userId++) {
            LikeFactory::new()
                ->forUser($userId)
                ->count(rand(2, 5))
                ->create();
        }

        echo "Created likes with factory system\n";
    }
}
