<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Investment>
 */
class InvestmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $investmentTypes = ['equity', 'debt', 'hybrid'];
        $statuses = ['pending', 'active', 'completed', 'cancelled'];
        
        $amount = $this->faker->numberBetween(5000, 100000);
        $expectedReturn = $amount * $this->faker->randomFloat(2, 0.1, 0.3); // 10-30% return

        return [
            'project_id' => Project::factory(),
            'investor_id' => User::factory()->state(['role' => 'investor']),
            'amount' => $amount,
            'investment_type' => $this->faker->randomElement($investmentTypes),
            'status' => $this->faker->randomElement($statuses),
            'expected_return' => $expectedReturn,
            'current_return' => $this->faker->optional(0.6)->randomFloat(2, 0, $expectedReturn),
            'investment_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'maturity_date' => $this->faker->dateTimeBetween('now', '+3 years'),
            'notes' => $this->faker->optional()->paragraph(),
            'contract_url' => $this->faker->optional()->url(),
            'payment_method' => $this->faker->randomElement(['bank_transfer', 'check', 'wire', 'crypto']),
        ];
    }

    /**
     * Indicate that the investment is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'investment_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'current_return' => $this->faker->randomFloat(2, 0, $attributes['expected_return'] * 0.7),
        ]);
    }

    /**
     * Indicate that the investment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'investment_date' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
            'current_return' => $attributes['expected_return'],
        ]);
    }

    /**
     * Indicate that the investment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'investment_date' => null,
            'current_return' => 0,
        ]);
    }

    /**
     * Indicate that the investment is equity type.
     */
    public function equity(): static
    {
        return $this->state(fn (array $attributes) => [
            'investment_type' => 'equity',
            'expected_return' => $attributes['amount'] * $this->faker->randomFloat(2, 0.15, 0.35),
        ]);
    }

    /**
     * Indicate that the investment is debt type.
     */
    public function debt(): static
    {
        return $this->state(fn (array $attributes) => [
            'investment_type' => 'debt',
            'expected_return' => $attributes['amount'] * $this->faker->randomFloat(2, 0.08, 0.15),
        ]);
    }
}
