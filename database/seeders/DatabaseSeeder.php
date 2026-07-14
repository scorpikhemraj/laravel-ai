<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed 10 Users
        \App\Models\User::factory(40)->create();

        // Ensure we have a default admin/test user
        if (!\App\Models\User::where('email', 'test@example.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'admin',
                'target_revenue' => 150000.00,
                'department' => 'Management',
                'commission_rate' => 0.15,
            ]);
        }

        // 2. Seed 30 Leads
        \App\Models\Lead::factory(100)->create();

        // 3. Seed 25 Opportunities
        \App\Models\Opportunity::factory(100)->create();
    }
}
