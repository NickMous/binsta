<?php

namespace NickMous\Binsta\Internals\Factories;

use DateTime;
use NickMous\Binsta\Entities\Comment;

class CommentFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'content' => $this->generateCommentContent(),
            'postId' => $this->faker()->numberBetween(1, 50), // Assuming posts from PostSeeder
            'userId' => $this->faker()->numberBetween(1, 15), // Assuming 15 users from UserSeeder
            'createdAt' => $this->faker()->dateTimeBetween('-2 months', 'now'),
            'updatedAt' => new DateTime(),
        ];
    }

    public function modelClass(): string
    {
        return Comment::class;
    }

    private function generateCommentContent(): string
    {
        $commentTypes = [
            'positive' => [
                'Great code! Thanks for sharing.',
                'This is exactly what I was looking for.',
                'Nice implementation! Very clean and readable.',
                'Love this approach, will definitely use it.',
                'Excellent solution! Works perfectly.',
                'Thanks for the detailed explanation.',
                'This saved me a lot of time. Much appreciated!',
                'Really helpful code snippet. Thanks!',
                'Perfect! This is the solution I needed.',
                'Amazing work! Very well documented.',
            ],
            'questions' => [
                'Could you explain how this works in more detail?',
                'What version of the language does this require?',
                'Is there a more efficient way to do this?',
                'How would you handle edge cases here?',
                'Can this be optimized further?',
                'What about error handling in this scenario?',
                'Would this work with larger datasets?',
                'Any performance considerations I should know about?',
                'How does this compare to other solutions?',
                'What dependencies does this code have?',
            ],
            'suggestions' => [
                'You might want to add some error handling here.',
                'Consider using a try-catch block for better error management.',
                'This could be refactored to be more modular.',
                'Have you considered using a different algorithm?',
                'Adding comments would make this even more helpful.',
                'You could optimize this by using built-in functions.',
                'Consider adding input validation.',
                'This pattern could be extracted into a helper function.',
                'What about adding unit tests for this?',
                'You might want to handle null values here.',
            ],
            'technical' => [
                'The time complexity here is O(n), which is good.',
                'This follows the single responsibility principle nicely.',
                'Good use of design patterns here.',
                'The separation of concerns is well implemented.',
                'This is a solid example of clean code principles.',
                'Nice use of functional programming concepts.',
                'The abstraction level is appropriate here.',
                'Good encapsulation of the business logic.',
                'This demonstrates good coding practices.',
                'The implementation is both readable and maintainable.',
            ],
            'learning' => [
                'I\'m new to this language. Can someone explain the syntax?',
                'Learning a lot from these examples. Keep them coming!',
                'As a beginner, this is very helpful to understand.',
                'This example cleared up my confusion. Thank you!',
                'I\'m still learning, but this makes sense to me.',
                'Great learning resource for beginners like me.',
                'This helped me understand the concept better.',
                'Perfect example for someone just starting out.',
                'I\'ll bookmark this for future reference.',
                'This is going into my code snippets collection!',
            ]
        ];

        $type = $this->faker()->randomElement(array_keys($commentTypes));
        $comments = $commentTypes[$type];

        return $this->faker()->randomElement($comments);
    }

    public function withPost(int $postId): static
    {
        return $this->state([
            'postId' => $postId,
        ]);
    }

    public function withUser(int $userId): static
    {
        return $this->state([
            'userId' => $userId,
        ]);
    }

    public function recent(): static
    {
        return $this->state([
            'createdAt' => $this->faker()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function positive(): static
    {
        $positiveComments = [
            'Great code! Thanks for sharing.',
            'This is exactly what I was looking for.',
            'Nice implementation! Very clean and readable.',
            'Love this approach, will definitely use it.',
            'Excellent solution! Works perfectly.',
        ];

        return $this->state([
            'content' => $this->faker()->randomElement($positiveComments),
        ]);
    }

    public function question(): static
    {
        $questionComments = [
            'Could you explain how this works in more detail?',
            'What version of the language does this require?',
            'Is there a more efficient way to do this?',
            'How would you handle edge cases here?',
            'Can this be optimized further?',
        ];

        return $this->state([
            'content' => $this->faker()->randomElement($questionComments),
        ]);
    }
}
