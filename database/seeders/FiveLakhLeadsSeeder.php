<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FiveLakhLeadsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable query log to save memory
        DB::connection()->disableQueryLog();

        // 1. Fetch user IDs
        $userIds = User::pluck('id')->toArray();
        if (empty($userIds)) {
            $user = User::factory()->create([
                'name' => 'Default Seeder User',
                'email' => 'seeder@example.com',
            ]);
            $userIds = [$user->id];
        }

        $totalRecords = 500000;
        $chunkSize = 1000;

        $this->command->info("Starting generation of {$totalRecords} lead records in chunks of {$chunkSize}...");

        // 2. Pre-generate a pool of values using faker to avoid Faker overhead in the hot loop
        $faker = \Faker\Factory::create();
        
        $this->command->info("Pre-generating data pools of size 1,000...");
        
        $poolSize = 1000;
        $firstNames = [];
        $lastNames = [];
        $companies = [];
        $jobTitles = [];
        $cities = [];
        $countries = [];
        $phoneNumbers = [];
        $addresses = [];
        $states = [];
        $postalCodes = [];
        $websites = [];
        $linkedinUrls = [];
        $notesPool = [];
        
        for ($i = 0; $i < $poolSize; $i++) {
            $firstNames[] = $faker->firstName;
            $lastNames[] = $faker->lastName;
            $companies[] = $faker->company;
            $jobTitles[] = $faker->jobTitle;
            $cities[] = $faker->city;
            $countries[] = $faker->country;
            $phoneNumbers[] = $faker->phoneNumber;
            $addresses[] = $faker->streetAddress;
            $states[] = $faker->state;
            $postalCodes[] = $faker->postcode;
            $websites[] = $faker->url;
            $linkedinUrls[] = 'https://www.linkedin.com/company/' . $faker->slug;
            $notesPool[] = $faker->sentence(10);
        }

        $statusOptions = ['new', 'contacted', 'qualified', 'lost'];
        $sourceOptions = ['website', 'referral', 'social_media', 'cold_call', 'advertising'];
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'example.com', 'company.com'];
        $industries = ['Technology', 'Finance', 'Healthcare', 'Education', 'Real Estate', 'Retail', 'Manufacturing', 'Energy'];

        // 3. Insert in chunks
        $now = now()->toDateTimeString();
        $bar = $this->command->getOutput()->createProgressBar($totalRecords / $chunkSize);
        $bar->start();

        for ($chunk = 0; $chunk < $totalRecords; $chunk += $chunkSize) {
            $data = [];
            for ($i = 0; $i < $chunkSize; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $company = $companies[array_rand($companies)];
                $domain = $domains[array_rand($domains)];
                
                // generate safe email based on name
                $emailName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName . '.' . $lastName));
                $email = $emailName . rand(10, 9999) . '@' . $domain;

                $status = $statusOptions[array_rand($statusOptions)];
                $convertedAt = $status === 'qualified' ? $now : null;

                $data[] = [
                    'first_name'          => $firstName,
                    'last_name'           => $lastName,
                    'email'               => $email,
                    'phone'               => $phoneNumbers[array_rand($phoneNumbers)],
                    'company'             => $company,
                    'title'               => $jobTitles[array_rand($jobTitles)],
                    'city'                => $cities[array_rand($cities)],
                    'country'             => $countries[array_rand($countries)],
                    'status'              => $status,
                    'source'              => $sourceOptions[array_rand($sourceOptions)],
                    'value'               => round(rand(50000, 2500000) / 100, 2), // $500.00 to $25,000.00
                    'is_favorite'         => (rand(1, 100) <= 10), // 10% chance of being favorite
                    'address'             => $addresses[array_rand($addresses)],
                    'state'               => $states[array_rand($states)],
                    'postal_code'         => $postalCodes[array_rand($postalCodes)],
                    'industry'            => $industries[array_rand($industries)],
                    'annual_revenue'      => round(rand(5000000, 1000000000) / 100, 2), // $50,000.00 to $10,000,000.00
                    'number_of_employees' => rand(5, 5000),
                    'website'             => $websites[array_rand($websites)],
                    'linkedin_url'        => $linkedinUrls[array_rand($linkedinUrls)],
                    'lead_score'          => rand(0, 100),
                    'notes'               => $notesPool[array_rand($notesPool)],
                    'converted_at'        => $convertedAt,
                    'user_id'             => $userIds[array_rand($userIds)],
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
            DB::table('leads')->insert($data);
            $bar->advance();
            
            // Free memory explicitly
            unset($data);
        }

        $bar->finish();
        $this->command->info("\nSeeding complete! Successfully seeded {$totalRecords} leads.");
    }
}
