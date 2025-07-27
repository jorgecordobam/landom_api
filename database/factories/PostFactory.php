<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['project_update', 'investment_opportunity', 'general_discussion', 'news', 'announcement'];

        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'type' => $this->faker->randomElement($types),
            'is_public' => $this->faker->boolean(80), // 80% chance of being public
            'author_id' => User::factory(),
            'likes_count' => $this->faker->numberBetween(0, 100),
            'comments_count' => $this->faker->numberBetween(0, 50),
            'featured' => $this->faker->boolean(10), // 10% chance of being featured
            'tags' => $this->faker->optional()->json([
                $this->faker->word(),
                $this->faker->word(),
                $this->faker->word()
            ]),
            'media_urls' => $this->faker->optional()->json([
                $this->faker->imageUrl(),
                $this->faker->imageUrl()
            ]),
        ];
    }

    /**
     * Indicate that the post is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the post is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the post is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    /**
     * Indicate that the post is a project update.
     */
    public function projectUpdate(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'project_update',
            'title' => 'Project Update: ' . $this->faker->sentence(3),
        ]);
    }

    /**
     * Indicate that the post is an investment opportunity.
     */
    public function investmentOpportunity(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'investment_opportunity',
            'title' => 'Investment Opportunity: ' . $this->faker->sentence(3),
        ]);
    }

    /**
     * Create a post with many likes.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'likes_count' => $this->faker->numberBetween(50, 200),
            'comments_count' => $this->faker->numberBetween(20, 80),
        ]);
    }
}
