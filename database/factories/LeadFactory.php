<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['new', 'contacted', 'qualified', 'lost']);
        $convertedAt = $status === 'qualified' ? fake()->dateTimeBetween('-30 days', 'now') : null;

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->company(),
            'title' => fake()->jobTitle(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'status' => $status,
            'source' => fake()->randomElement(['website', 'referral', 'social_media', 'cold_call', 'advertising']),
            'value' => fake()->randomFloat(2, 500, 25000),
            'address' => fake()->streetAddress(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'industry' => fake()->randomElement(['Technology', 'Finance', 'Healthcare', 'Education', 'Real Estate', 'Retail', 'Manufacturing', 'Energy']),
            'annual_revenue' => fake()->randomFloat(2, 50000, 10000000),
            'number_of_employees' => fake()->numberBetween(5, 5000),
            'website' => fake()->url(),
            'linkedin_url' => 'https://www.linkedin.com/company/' . fake()->slug(),
            'lead_score' => fake()->numberBetween(0, 100),
            'notes' => fake()->sentence(10),
            'converted_at' => $convertedAt,
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
        ];
    }
}
