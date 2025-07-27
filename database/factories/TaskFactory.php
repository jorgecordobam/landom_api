<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phases = ['planning', 'demolition', 'foundation', 'framing', 'electrical', 'plumbing', 'drywall', 'flooring', 'painting', 'finishing'];
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        return [
            'project_id' => Project::factory(),
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'phase' => $this->faker->randomElement($phases),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
            'estimated_hours' => $this->faker->numberBetween(4, 120),
            'actual_hours' => $this->faker->optional(0.6)->numberBetween(4, 150),
            'estimated_cost' => $this->faker->numberBetween(500, 10000),
            'actual_cost' => $this->faker->optional(0.6)->numberBetween(400, 12000),
            'start_date' => $this->faker->optional()->dateTimeBetween('-3 months', '+1 month'),
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'completion_date' => $this->faker->optional(0.4)->dateTimeBetween('-1 month', 'now'),
            'assigned_to' => $this->faker->optional()->randomElement([
                User::factory()->state(['role' => 'worker']),
                User::factory()->state(['role' => 'constructor']),
            ]),
            'notes' => $this->faker->optional()->paragraph(),
            'dependencies' => $this->faker->optional()->json([
                'prerequisite_tasks' => [],
                'blocking_tasks' => []
            ]),
        ];
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completion_date' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'actual_hours' => $this->faker->numberBetween($attributes['estimated_hours'] * 0.8, $attributes['estimated_hours'] * 1.3),
            'actual_cost' => $this->faker->numberBetween($attributes['estimated_cost'] * 0.8, $attributes['estimated_cost'] * 1.2),
        ]);
    }

    /**
     * Indicate that the task is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the task is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'start_date' => null,
            'completion_date' => null,
            'actual_hours' => null,
            'actual_cost' => null,
        ]);
    }

    /**
     * Indicate that the task is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the task is assigned to a specific user.
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }
}
