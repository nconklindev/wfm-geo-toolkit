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
        $john = User::factory()->create([
            'username' => 'johnwick',
            'email' => 'john.wick@example.com',
        ]);

        $manager = User::factory()->create([
            'username' => 'mrmanager',
            'email' => 'manager@continental.com',
        ]);

        $concierge = User::factory()->create([
            'username' => 'concierge',
            'email' => 'concierge@example.com',
        ]);

        KnownPlace::factory(20)
            ->for($john)
            ->create();

        KnownPlace::factory(50)
            ->for($manager)
            ->create();

        // Add some known places for the test user too
        KnownPlace::factory(30)
            ->for($concierge)
            ->create();


//        $this->call([BusinessStructureTypeSeeder::class]);
    }
}
