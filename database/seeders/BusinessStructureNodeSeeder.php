<?php

namespace Database\Seeders;

use App\Models\BusinessStructureNode;
use App\Models\BusinessStructureType;
use Exception;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BusinessStructureNodeSeeder extends Seeder
{
    public function run(): void
    {
        // Disable Scout indexing during seeding
        BusinessStructureNode::withoutSyncingToSearch(function () {
            $this->command->info('Starting business structure node seeding without search sync...');

            DB::beginTransaction();

            try {
                $faker = Factory::create();

                // Get types ordered by hierarchy level (ascending)
                $types = BusinessStructureType::orderBy('hierarchy_order')->get();
                $this->command->info("Found ".$types->count()." business structure types");

                // Get all the types we'll need and verify they exist
                $typeNames = ['Company', 'Region', 'State', 'Location', 'Store', 'Department', 'Job'];
                $typeMap = [];

                foreach ($typeNames as $typeName) {
                    $type = $types->where('name', $typeName)->first();
                    if (!$type) {
                        $this->command->error("Business structure type '{$typeName}' not found! Please ensure all required types exist.");
                        return;
                    }
                    $typeMap[$typeName] = $type;
                    $this->command->info("Found type: {$typeName} with ID: {$type->id}");
                }

                $this->command->info('Creating nodes according to proper hierarchy...');

                // Level 1: Companies (top level nodes)
                $this->command->info("Creating Company nodes...");
                $companies = [];
                $companyType = $typeMap['Company'];

                for ($i = 0; $i < 3; $i++) { // Reduced to 3 companies for testing
                    $companyName = $faker->company;
                    $company = BusinessStructureNode::create([
                        'name' => $companyName,
                        'description' => "{$companyName} - Top level organization",
                        'business_structure_type_id' => $companyType->id,
                        'parent_id' => null, // No parent for top level
                        'structure_hash' => md5($companyName.$companyType->id.now()->timestamp),
                        'path' => null,
                        'path_hierarchy' => [],
                        'start_date' => now(),
                        'end_date' => now()->addYears(5),
                    ]);
                    $companies[] = $company;
                }
                $this->command->info("Created ".count($companies)." company nodes");

                // Level 2: Regions under companies
                $this->command->info("Creating Region nodes...");
                $regions = [];
                $regionType = $typeMap['Region'];

                foreach ($companies as $company) {
                    // Create 2-3 regions per company
                    $regionsPerCompany = rand(2, 3);
                    for ($i = 0; $i < $regionsPerCompany; $i++) {
                        $regionName = $this->getRegionName($faker);
                        $region = BusinessStructureNode::create([
                            'name' => $regionName,
                            'description' => "{$regionName} - Regional division",
                            'business_structure_type_id' => $regionType->id,
                            'parent_id' => $company->id,
                            'structure_hash' => md5($regionName.$company->id.$regionType->id.now()->timestamp),
                            'path' => (string) $company->id,
                            'path_hierarchy' => [$company->name],
                            'start_date' => now(),
                            'end_date' => now()->addYears(5),
                        ]);
                        if (!$region) {
                            $this->command->error("Failed to create region node");
                            continue;
                        }
                        $regions[] = $region;
                    }
                }
                $this->command->info("Created ".count($regions)." region nodes");

                // Level 3: States under regions
                $this->command->info("Creating State nodes...");
                $states = [];
                $stateType = $typeMap['State'];

                foreach ($regions as $region) {
                    // Create 2-3 states per region (reduced for testing)
                    $statesPerRegion = rand(2, 3);
                    for ($i = 0; $i < $statesPerRegion; $i++) {
                        $stateName = $faker->state;
                        $state = BusinessStructureNode::create([
                            'name' => $stateName,
                            'description' => "{$stateName} - State division",
                            'business_structure_type_id' => $stateType->id,
                            'parent_id' => $region->id,
                            'structure_hash' => md5($stateName.$region->id.$stateType->id.now()->timestamp),
                            'path' => $region->path.'.'.$region->id,
                            'path_hierarchy' => array_merge($region->path_hierarchy, [$region->name]),
                            'start_date' => now(),
                            'end_date' => now()->addYears(5),
                        ]);
                        if (!$state) {
                            $this->command->error("Failed to create state node");
                            continue;
                        }
                        $states[] = $state;
                    }
                }
                $this->command->info("Created ".count($states)." state nodes");

                // Level 4: Locations under states
                $this->command->info("Creating Location nodes...");
                $locations = [];
                $locationType = $typeMap['Location'];

                foreach ($states as $state) {
                    // Create 1-2 locations per state (reduced for testing)
                    $locationsPerState = rand(1, 2);
                    for ($i = 0; $i < $locationsPerState; $i++) {
                        $locationName = $faker->city." Location";
                        $location = BusinessStructureNode::create([
                            'name' => $locationName,
                            'description' => "{$locationName} - Location division",
                            'business_structure_type_id' => $locationType->id,
                            'parent_id' => $state->id,
                            'structure_hash' => md5($locationName.$state->id.$locationType->id.now()->timestamp),
                            'path' => $state->path.'.'.$state->id,
                            'path_hierarchy' => array_merge($state->path_hierarchy, [$state->name]),
                            'start_date' => now(),
                            'end_date' => now()->addYears(5),
                        ]);
                        if (!$location) {
                            $this->command->error("Failed to create location node");
                            continue;
                        }
                        $locations[] = $location;
                    }
                }
                $this->command->info("Created ".count($locations)." location nodes");

                // Level 5: Stores under locations
                $this->command->info("Creating Store nodes...");
                $stores = [];
                $storeType = $typeMap['Store'];

                foreach ($locations as $location) {
                    // Create 1-2 stores per location (reduced for testing)
                    $storesPerLocation = rand(1, 2);
                    for ($i = 0; $i < $storesPerLocation; $i++) {
                        $storeName = $faker->company." Store";
                        $store = BusinessStructureNode::create([
                            'name' => $storeName,
                            'description' => "{$storeName} - Retail store",
                            'business_structure_type_id' => $storeType->id,
                            'parent_id' => $location->id,
                            'structure_hash' => md5($storeName.$location->id.$storeType->id.now()->timestamp),
                            'path' => $location->path.'.'.$location->id,
                            'path_hierarchy' => array_merge($location->path_hierarchy, [$location->name]),
                            'start_date' => now(),
                            'end_date' => now()->addYears(5),
                        ]);
                        if (!$store) {
                            $this->command->error("Failed to create store node");
                            continue;
                        }
                        $stores[] = $store;
                        $this->command->info("Created store: ".$storeName." with ID: ".$store->id);
                    }
                }
                $this->command->info("Created ".count($stores)." store nodes");

                // Level 6: Departments under stores
                $this->command->info("Creating Department nodes...");
                $departments = [];
                $departmentType = $typeMap['Department'];

                foreach ($stores as $store) {
                    $this->command->info("Creating departments for store ID: ".$store->id);

                    // Create 1-2 departments per store (reduced for testing)
                    $departmentsPerStore = rand(1, 2);
                    for ($i = 0; $i < $departmentsPerStore; $i++) {
                        $departmentName = $this->getDepartmentName($faker);

                        try {
                            $department = new BusinessStructureNode([
                                'name' => $departmentName,
                                'description' => "{$departmentName} - Store department",
                                'business_structure_type_id' => $departmentType->id,
                                'parent_id' => $store->id,
                                'structure_hash' => md5($departmentName.$store->id.$departmentType->id.now()->timestamp),
                                'path' => $store->path.'.'.$store->id,
                                'path_hierarchy' => array_merge($store->path_hierarchy, [$store->name]),
                                'start_date' => now(),
                                'end_date' => now()->addYears(5),
                            ]);

                            $success = $department->save();

                            if (!$success) {
                                $this->command->error("Failed to save department: ".$departmentName);
                                continue;
                            }

                            $departments[] = $department;
                            $this->command->info("Created department: ".$departmentName." with ID: ".$department->id);
                        } catch (Exception $e) {
                            $this->command->error("Exception creating department: ".$e->getMessage());
                            Log::error("Error creating department: ".$e->getMessage());
                        }
                    }
                }
                $this->command->info("Created ".count($departments)." department nodes");

                // Level 7: Jobs under departments
                $this->command->info("Creating Job nodes...");
                $jobCount = 0;
                $jobType = $typeMap['Job'];

                foreach ($departments as $department) {
                    $this->command->info("Creating jobs for department ID: ".$department->id);

                    // Create 1-2 jobs per department (reduced for testing)
                    $jobsPerDepartment = rand(1, 2);
                    for ($i = 0; $i < $jobsPerDepartment; $i++) {
                        $jobName = $this->getJobName($faker, $department->name);

                        try {
                            $job = new BusinessStructureNode([
                                'name' => $jobName,
                                'description' => "{$jobName} - Position in {$department->name}",
                                'business_structure_type_id' => $jobType->id,
                                'parent_id' => $department->id,
                                'structure_hash' => md5($jobName.$department->id.$jobType->id.now()->timestamp),
                                'path' => $department->path.'.'.$department->id,
                                'path_hierarchy' => array_merge($department->path_hierarchy, [$department->name]),
                                'start_date' => now(),
                                'end_date' => now()->addYears(5),
                            ]);

                            $success = $job->save();

                            if (!$success) {
                                $this->command->error("Failed to save job: ".$jobName);
                                continue;
                            }

                            $jobCount++;
                            $this->command->info("Created job: ".$jobName." with ID: ".$job->id);
                        } catch (Exception $e) {
                            $this->command->error("Exception creating job: ".$e->getMessage());
                            Log::error("Error creating job: ".$e->getMessage());
                        }
                    }
                }
                $this->command->info("Created ".$jobCount." job nodes");

                DB::commit();
                $this->command->info('Business structure hierarchy created successfully with all 7 levels!');

            } catch (Exception $e) {
                DB::rollBack();
                $this->command->error("Failed to seed business structure nodes: ".$e->getMessage());
                Log::error("Failed to seed business structure nodes: ".$e->getMessage());
            }
        });
    }

    private function getRegionName($faker): string
    {
        $regions = [
            'North America',
            'South America',
            'Europe',
            'Asia Pacific',
            'Middle East',
            'Africa',
            'Central America',
            'Eastern Europe',
            'Western Europe',
            'Northern Europe',
            'Southern Europe',
            'Southeast Asia',
            'East Asia',
            'South Asia',
            'Central Asia',
            'Oceania',
            'Caribbean',
            'Nordic',
            'Mediterranean',
            'Balkan',
            'Scandinavian',
            'Alpine',
            'Baltic',
            'Benelux',
            'Caucasus'
        ];

        return $faker->randomElement($regions);
    }

    private function getDepartmentName($faker): string
    {
        $departments = [
            'Sales',
            'Marketing',
            'Customer Service',
            'Finance',
            'Human Resources',
            'IT',
            'Operations',
            'Research & Development',
            'Accounting',
            'Legal',
            'Warehouse',
            'Logistics',
            'Produce',
            'Bakery',
            'Deli',
            'Grocery',
            'Meat & Seafood',
            'Dairy',
            'Frozen Foods',
            'Electronics',
            'Apparel',
            'Home Goods',
            'Beauty & Personal Care',
            'Pharmacy',
            'Security'
        ];

        return $faker->randomElement($departments);
    }

    private function getJobName($faker, $departmentName): string
    {
        $commonJobs = [
            'Manager',
            'Associate',
            'Specialist',
            'Assistant',
            'Coordinator',
            'Supervisor',
            'Team Lead',
            'Director',
            'Consultant',
            'Analyst',
            'Representative',
            'Technician',
            'Clerk',
            'Administrator'
        ];

        return $departmentName.' '.$faker->randomElement($commonJobs);
    }
}
