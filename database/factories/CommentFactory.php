<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'author_id' => User::factory(),
            'content' => $this->faker->paragraph(),
            'likes_count' => $this->faker->numberBetween(0, 20),
            'parent_id' => null, // Top-level comment by default
        ];
    }

    /**
     * Indicate that the comment is a reply to another comment.
     */
    public function reply($parentCommentId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentCommentId ?? Comment::factory(),
        ]);
    }

    /**
     * Create a comment with many likes.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'likes_count' => $this->faker->numberBetween(10, 50),
        ]);
    }
}
