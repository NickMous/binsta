<?php

namespace NickMous\Binsta\Internals\Factories;

use DateTime;
use NickMous\Binsta\Entities\Like;

class LikeFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'userId' => $this->faker()->numberBetween(1, 15), // Assuming 15 users from UserSeeder
            'postId' => $this->faker()->numberBetween(1, 50), // Assuming posts from PostSeeder
            'createdAt' => $this->faker()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    public function modelClass(): string
    {
        return Like::class;
    }

    public function forUser(int $userId): static
    {
        return $this->state([
            'userId' => $userId,
        ]);
    }

    public function forPost(int $postId): static
    {
        return $this->state([
            'postId' => $postId,
        ]);
    }

    public function between(int $userId, int $postId): static
    {
        return $this->state([
            'userId' => $userId,
            'postId' => $postId,
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
            'createdAt' => $this->faker()->dateTimeBetween('-6 months', '-3 months'),
        ]);
    }
}
