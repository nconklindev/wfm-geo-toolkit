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
            'description' => $this->faker->company(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'radius' => $this->faker->randomNumber(2, true),
            'location_path' => $this->faker->word(),
            'gps_accuracy_threshold' => $this->faker->randomNumber(2, true),
            'validation_order' => ['gps', 'wifi'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
