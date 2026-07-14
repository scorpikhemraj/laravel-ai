<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Opportunity>
 */
class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stage = fake()->randomElement(['prospecting', 'qualification', 'proposal', 'negotiation', 'closed_won', 'closed_lost']);
        
        $probabilities = [
            'prospecting' => 10,
            'qualification' => 30,
            'proposal' => 50,
            'negotiation' => 80,
            'closed_won' => 100,
            'closed_lost' => 0,
        ];
        
        $probability = $probabilities[$stage];
        $lostReason = $stage === 'closed_lost' ? fake()->randomElement(['Competitor Price', 'Lack of features', 'Timing/Budget', 'No response']) : null;
        $actualCloseDate = in_array($stage, ['closed_won', 'closed_lost']) ? fake()->dateTimeBetween('-30 days', 'now') : null;

        $dealName = fake()->randomElement(['Software Deal', 'Integration Project', 'Enterprise License', 'Consulting Package']);
        $company = fake()->company();

        return [
            'title' => "{$company} - {$dealName}",
            'lead_id' => Lead::inRandomOrder()->first()?->id, // Nullable / Random association
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'stage' => $stage,
            'amount' => fake()->randomFloat(2, 5000, 150000),
            'probability' => $probability,
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'type' => fake()->randomElement(['new_business', 'existing_business']),
            'lost_reason' => $lostReason,
            'expected_close_date' => fake()->dateTimeBetween('now', '+90 days'),
            'actual_close_date' => $actualCloseDate,
        ];
    }
}
