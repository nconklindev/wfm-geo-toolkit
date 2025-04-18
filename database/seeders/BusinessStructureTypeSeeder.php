<?php

namespace Database\Seeders;

use App\Models\BusinessStructureType;
use App\Services\BusinessStructureTypeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BusinessStructureTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id' => 1,
                'name' => 'Company',
                'description' => 'Top level organizational unit',
                'order' => 100,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Region',
                'description' => 'Geographic region',
                'order' => 200,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'State',
                'description' => 'State or province',
                'order' => 300,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Location',
                'description' => 'Geographic location',
                'order' => 400,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Store',
                'description' => 'Retail outlet',
                'order' => 500,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'name' => 'Department',
                'description' => 'Organizational department',
                'order' => 600,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'name' => 'Job',
                'description' => 'Job position',
                'order' => 700,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Get total number of types for color distribution
        $totalTypes = count($types);

        foreach ($types as $index => $type) {
            // Generate distinct hex color based on order
            $type['color'] = BusinessStructureTypeService::generateDistinctColor(
                $index,
                $totalTypes
            );
            $this->command->info('Setting Hex Color'.$type['color'].' for '.$type['name']);

            BusinessStructureType::updateOrCreate(['id' => $type['id']], $type);
        }
    }
}
