<?php

namespace NickMous\Binsta\Internals\Factories;

use DateTime;
use NickMous\Binsta\Entities\User;

class UserFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker()->name(),
            'username' => $this->faker()->userName(),
            'email' => $this->faker()->unique()->safeEmail(),
            'password' => 'password123', // Default test password
            'biography' => $this->faker()->optional(0.7)->sentence(10),
            'profilePicture' => $this->faker()->optional(0.3)->imageUrl(200, 200, 'people'),
            'createdAt' => $this->faker()->dateTimeBetween('-1 year', 'now'),
            'updatedAt' => new DateTime(),
        ];
    }

    public function modelClass(): string
    {
        return User::class;
    }

    public function withoutBiography(): static
    {
        return $this->state([
            'biography' => null,
        ]);
    }

    public function withoutProfilePicture(): static
    {
        return $this->state([
            'profilePicture' => null,
        ]);
    }
}
