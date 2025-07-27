<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $verificationStatuses = ['pending', 'verified', 'rejected'];
        $companyTypes = ['construction', 'development', 'architecture', 'engineering', 'consulting'];

        return [
            'name' => $this->faker->company(),
            'owner_id' => User::factory()->state(['role' => 'constructor']),
            'type' => $this->faker->randomElement($companyTypes),
            'registration_number' => $this->faker->unique()->numerify('REG-#########'),
            'tax_id' => $this->faker->unique()->numerify('TAX-#########'),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->companyEmail,
            'website' => $this->faker->optional()->url,
            'description' => $this->faker->paragraph(3),
            'verification_status' => $this->faker->randomElement($verificationStatuses),
            'verification_date' => $this->faker->optional(0.6)->dateTimeBetween('-1 year', 'now'),
            'verification_notes' => $this->faker->optional()->paragraph(),
            'license_number' => $this->faker->optional()->numerify('LIC-#######'),
            'license_expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'insurance_policy_number' => $this->faker->optional()->numerify('INS-#########'),
            'insurance_expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'documents' => $this->faker->optional()->json([
                'registration_certificate' => 'documents/companies/reg_cert_' . $this->faker->uuid . '.pdf',
                'license_certificate' => 'documents/companies/license_' . $this->faker->uuid . '.pdf',
                'insurance_certificate' => 'documents/companies/insurance_' . $this->faker->uuid . '.pdf',
            ]),
        ];
    }

    /**
     * Indicate that the company is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'verified',
            'verification_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'verification_notes' => 'All documents verified successfully.',
        ]);
    }

    /**
     * Indicate that the company is pending verification.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'pending',
            'verification_date' => null,
            'verification_notes' => null,
        ]);
    }

    /**
     * Indicate that the company verification was rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'rejected',
            'verification_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'verification_notes' => 'Incomplete documentation provided.',
        ]);
    }

    /**
     * Indicate that the company is a construction company.
     */
    public function construction(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'construction',
            'license_number' => 'CONST-' . $this->faker->numerify('#######'),
        ]);
    }

    /**
     * Indicate that the company is a development company.
     */
    public function development(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'development',
            'license_number' => 'DEV-' . $this->faker->numerify('#######'),
        ]);
    }
}
