<?php

namespace Database\Factories;

use App\Models\Bug;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BugFactory extends Factory
{
    protected $model = Bug::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'steps_to_reproduce' => fake()->optional()->paragraph(),
            'severity' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => fake()->randomElement(['open', 'in_progress', 'resolved', 'closed']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'environment' => fake()->randomElement(['production', 'staging', 'development']),
            'assigned_to' => User::inRandomOrder()->first()?->id,
            'reported_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'resolved_at' => fake()->optional()->dateTime(),
        ];
    }
}
