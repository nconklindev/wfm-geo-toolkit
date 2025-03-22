<?php

namespace Database\Seeders;

use App\Models\KnownPlace;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $john = User::factory()->create([
            'name' => 'John Wick',
            'email' => 'john.wick@example.com',
        ]);

        $manager = User::factory()->create([
            'name' => 'Mr Manager',
            'email' => 'manager@continental.com',
        ]);

        KnownPlace::factory(20)
            ->for($john)
            ->create();

        KnownPlace::factory(100)
            ->for($manager)
            ->create();
    }
}
