<?php

namespace Database\Factories;

use App\Models\KnownIpAddress;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class KnownIpAddressFactory extends Factory
{
    protected $model = KnownIpAddress::class;

    public function definition(): array
    {
        return [
            'start' => $this->faker->ipv4(),
            'end' => $this->faker->ipv4(),
            'name' => $this->faker->name(),
            'description' => $this->faker->title(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
