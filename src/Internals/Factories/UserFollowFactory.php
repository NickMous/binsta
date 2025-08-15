<?php

namespace NickMous\Binsta\Internals\Factories;

use DateTime;
use NickMous\Binsta\Entities\UserFollow;
use NickMous\Binsta\Entities\User;
use RedBeanPHP\R;

class UserFollowFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        // Get random users for follower/following relationship
        $userIds = $this->getRandomUserIds();

        return [
            'followerId' => $userIds[0],
            'followingId' => $userIds[1],
            'createdAt' => $this->faker()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function modelClass(): string
    {
        return UserFollow::class;
    }

    public function between(int $followerId, int $followingId): static
    {
        return $this->state([
            'followerId' => $followerId,
            'followingId' => $followingId,
        ]);
    }

    public function recent(): static
    {
        return $this->state([
            'createdAt' => $this->faker()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state([
            'createdAt' => $this->faker()->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }

    /** @return array<int, int> */
    private function getRandomUserIds(): array
    {
        // Get all user IDs from the database
        $userIds = R::getCol('SELECT id FROM ' . User::getTableName() . ' ORDER BY id');

        if (count($userIds) < 2) {
            throw new \Exception('Need at least 2 users in database to create follow relationships');
        }

        // Pick two different random users
        $shuffled = array_values($userIds);
        shuffle($shuffled);

        return [(int)$shuffled[0], (int)$shuffled[1]];
    }
}
