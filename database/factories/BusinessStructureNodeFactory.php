<?php

namespace Database\Factories;

use App\Models\BusinessStructureNode;
use App\Models\BusinessStructureType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BusinessStructureNodeFactory extends Factory
{
    protected $model = BusinessStructureNode::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'parent_id' => $this->faker->randomNumber(),
            'path' => $this->faker->word(),
            'path_hierarchy' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
