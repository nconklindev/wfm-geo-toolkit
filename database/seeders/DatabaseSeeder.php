<?php

namespace Database\Seeders;

use App\Models\KnownIpAddress;
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

        $john->knownIpAddresses()->createMany(
            KnownIpAddress::factory()->count(5)->make()->toArray()
        );
    }
}
