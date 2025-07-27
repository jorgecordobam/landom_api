<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $projectTypes = ['fix_and_flip', 'new_construction', 'renovation', 'commercial'];
        $statuses = ['planning', 'seeking_investment', 'active', 'completed', 'cancelled', 'suspended'];
        $riskLevels = ['low', 'medium', 'high'];

        $estimatedBudget = $this->faker->numberBetween(50000, 1000000);
        $investmentGoal = $estimatedBudget * 0.7; // 70% of budget
        $currentInvestment = $this->faker->numberBetween(0, $investmentGoal);

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'location' => $this->faker->address,
            'estimated_budget' => $estimatedBudget,
            'actual_budget' => null,
            'estimated_duration' => $this->faker->numberBetween(30, 365),
            'actual_duration' => null,
            'start_date' => $this->faker->optional()->dateTimeBetween('-6 months', '+1 month'),
            'end_date' => $this->faker->optional()->dateTimeBetween('+1 month', '+18 months'),
            'completion_date' => null,
            'project_type' => $this->faker->randomElement($projectTypes),
            'status' => $this->faker->randomElement($statuses),
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'investment_goal' => $investmentGoal,
            'current_investment' => $currentInvestment,
            'min_investment' => $this->faker->numberBetween(1000, 10000),
            'roi_percentage' => $this->faker->randomFloat(2, 8, 25),
            'risk_level' => $this->faker->randomElement($riskLevels),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the project is seeking investment.
     */
    public function seekingInvestment(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'seeking_investment',
            'current_investment' => $this->faker->numberBetween(0, $attributes['investment_goal'] * 0.5),
        ]);
    }

    /**
     * Indicate that the project is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'progress_percentage' => $this->faker->numberBetween(10, 80),
        ]);
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress_percentage' => 100,
            'completion_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'actual_budget' => $this->faker->numberBetween($attributes['estimated_budget'] * 0.8, $attributes['estimated_budget'] * 1.2),
            'actual_duration' => $this->faker->numberBetween($attributes['estimated_duration'] * 0.8, $attributes['estimated_duration'] * 1.3),
        ]);
    }

    /**
     * Indicate that the project is fully funded.
     */
    public function fullyFunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_investment' => $attributes['investment_goal'],
        ]);
    }

    /**
     * Indicate that the project is a fix and flip.
     */
    public function fixAndFlip(): static
    {
        return $this->state(fn (array $attributes) => [
            'project_type' => 'fix_and_flip',
            'estimated_duration' => $this->faker->numberBetween(60, 180),
            'roi_percentage' => $this->faker->randomFloat(2, 15, 30),
        ]);
    }

    /**
     * Indicate that the project is new construction.
     */
    public function newConstruction(): static
    {
        return $this->state(fn (array $attributes) => [
            'project_type' => 'new_construction',
            'estimated_duration' => $this->faker->numberBetween(180, 500),
            'estimated_budget' => $this->faker->numberBetween(200000, 2000000),
        ]);
    }
}
