<?php

namespace Database\Seeders;

use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        foreach ([
            ['name' => 'Basic', 'fee' => 500, 'validity_months' => 12, 'benefits' => 'Member directory access'],
            ['name' => 'Silver', 'fee' => 1000, 'validity_months' => 12, 'benefits' => 'Basic benefits plus event discounts'],
            ['name' => 'Gold', 'fee' => 1500, 'validity_months' => 12, 'benefits' => 'Silver benefits plus priority support'],
            ['name' => 'Platinum', 'fee' => 2500, 'validity_months' => 12, 'benefits' => 'All benefits plus exclusive programs'],
        ] as $level) {
            MembershipLevel::updateOrCreate(['name' => $level['name']], $level);
        }

        foreach ([
            ['name' => 'Student', 'level' => 'Education', 'fee' => 250],
            ['name' => 'Regular', 'level' => 'Standard', 'fee' => 500],
            ['name' => 'Senior Citizen', 'level' => 'Community', 'fee' => 350],
            ['name' => 'Corporate', 'level' => 'Business', 'fee' => 1500],
        ] as $category) {
            MembershipCategory::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
