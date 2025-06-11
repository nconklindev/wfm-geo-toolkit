<?php

namespace Database\Factories;

use App\Models\KnownPlace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class KnownPlaceFactory extends Factory
{
    protected $model = KnownPlace::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'description' => $this->faker->streetAddress(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'radius' => $this->faker->randomNumber(2, true),
            'locations' => function () {
                $locations = [];

                // Create 1-3 random "X/Y/Z" patterns
                $count = $this->faker->numberBetween(1, 3);

                for ($i = 0; $i < $count; $i++) {
                    // Always create a pattern with all 3 parts: company/city/postcode
                    $locations[] = implode('/', [
                        $this->faker->companySuffix(),
                        $this->faker->city(),
                        $this->faker->postcode(),
                        $this->faker->jobTitle(),
                    ]);
                }
                return $locations;
            },
            'accuracy' => $this->faker->randomNumber(2, true),
            'validation_order' => ['gps', 'wifi'],
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
