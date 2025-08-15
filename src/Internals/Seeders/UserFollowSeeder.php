<?php

namespace NickMous\Binsta\Internals\Seeders;

use NickMous\Binsta\Internals\Factories\UserFollowFactory;
use NickMous\Binsta\Entities\User;
use RedBeanPHP\R;

class UserFollowSeeder extends Seeder
{
    public function run(): void
    {
        // Get all user IDs
        $userIds = R::getCol('SELECT id FROM ' . User::getTableName() . ' ORDER BY id');

        if (count($userIds) < 2) {
            echo "Not enough users to create follow relationships. Need at least 2 users.\n";
            return;
        }

        // Create some random follow relationships
        echo "Creating random follow relationships...\n";
        UserFollowFactory::new()
            ->count(20)
            ->create();

        // Create some specific relationships for testing
        if (count($userIds) >= 3) {
            $firstUserId = (int)$userIds[0];
            $secondUserId = (int)$userIds[1];
            $thirdUserId = (int)$userIds[2];

            // Make first user follow second user
            UserFollowFactory::new()
                ->between($firstUserId, $secondUserId)
                ->recent()
                ->create();

            // Make second user follow third user
            UserFollowFactory::new()
                ->between($secondUserId, $thirdUserId)
                ->create();

            // Make third user follow first user (creates a circle)
            UserFollowFactory::new()
                ->between($thirdUserId, $firstUserId)
                ->old()
                ->create();

            echo "Created specific test relationships between first 3 users\n";
        }

        // Create some mutual follows (users who follow each other)
        if (count($userIds) >= 4) {
            $user1 = (int)$userIds[0];
            $user2 = (int)$userIds[1];

            // Create mutual follow relationship
            UserFollowFactory::new()
                ->between($user1, $user2)
                ->recent()
                ->create();

            UserFollowFactory::new()
                ->between($user2, $user1)
                ->recent()
                ->create();

            echo "Created mutual follow relationship\n";
        }

        echo "UserFollow seeding completed\n";
    }
}
