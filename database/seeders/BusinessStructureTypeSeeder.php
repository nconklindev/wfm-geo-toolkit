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
                'hierarchy_order' => 100,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Region',
                'description' => 'Geographic region',
                'hierarchy_order' => 200,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'State',
                'description' => 'State or province',
                'hierarchy_order' => 300,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Location',
                'description' => 'Geographic location',
                'hierarchy_order' => 400,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Store',
                'description' => 'Retail outlet',
                'hierarchy_order' => 500,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'name' => 'Department',
                'description' => 'Organizational department',
                'hierarchy_order' => 600,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'name' => 'Job',
                'description' => 'Job position',
                'hierarchy_order' => 700,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Get total number of types for color distribution
        $totalTypes = count($types);

        foreach ($types as $index => $type) {
            // Generate distinct hex color based on hierarchy_order
            $type['hex_color'] = BusinessStructureTypeService::generateDistinctColor(
                $index,
                $totalTypes
            );
            $this->command->info('Setting Hex Color'.$type['hex_color'].' for '.$type['name']);

            BusinessStructureType::updateOrCreate(['id' => $type['id']], $type);
        }
    }
}
