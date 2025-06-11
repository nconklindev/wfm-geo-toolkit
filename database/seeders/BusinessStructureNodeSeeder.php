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
    // Constants for controlling distribution
    const TOTAL_NODES = 100;

    // Distribution percentages (must sum to 100)
    const DISTRIBUTION = [
        'Company' => 0.5,      // 0.5% = ~50 companies
        'Region' => 1.5,       // 1.5% = ~150 regions
        'State' => 3,          // 3% = ~300 states
        'Location' => 5,       // 5% = ~500 locations
        'Store' => 10,         // 10% = ~1,000 stores
        'Department' => 30,    // 30% = ~3,000 departments
        'Job' => 50,           // 50% = ~5,000 jobs
    ];

    // Track created nodes
    private $nodesByType = [];
    private $totalCreated = 0;
    private $targetCounts = [];

    public function run(): void
    {
        // Calculate target counts for each type
        foreach (self::DISTRIBUTION as $type => $percentage) {
            $this->targetCounts[$type] = (int) ceil((self::TOTAL_NODES * $percentage) / 100);
            $this->nodesByType[$type] = [];
        }

        // Disable Scout indexing during seeding
        BusinessStructureNode::withoutSyncingToSearch(function () {
            $this->command->info('Starting business structure node seeding without search sync...');
            $this->command->info('Target: '.self::TOTAL_NODES.' total nodes');

            foreach ($this->targetCounts as $type => $count) {
                $this->command->info("Target for {$type}: {$count} nodes");
            }

            DB::beginTransaction();

            try {
                $faker = Factory::create();

                // Get types ordered by hierarchy level
                $types = BusinessStructureType::orderBy('order')->get();
                $typeMap = [];

                // Validate types exist
                $typeNames = array_keys(self::DISTRIBUTION);
                foreach ($typeNames as $typeName) {
                    $type = $types->where('name', $typeName)->first();
                    if (!$type) {
                        $this->command->error("Business structure type '{$typeName}' not found!");
                        return;
                    }
                    $typeMap[$typeName] = $type;
                }

                $this->command->info('Creating nodes according to hierarchy...');

                // Level 1: Companies (top level)
                $this->createCompanies($faker, $typeMap['Company']);

                // Level 2: Regions
                $this->createRegions($faker, $typeMap['Region']);

                // Level 3: States
                $this->createStates($faker, $typeMap['State']);

                // Level 4: Locations
                $this->createLocations($faker, $typeMap['Location']);

                // Level 5: Stores
                $this->createStores($faker, $typeMap['Store']);

                // Level 6: Departments
                $this->createDepartments($faker, $typeMap['Department']);

                // Level 7: Jobs
                $this->createJobs($faker, $typeMap['Job']);

                DB::commit();

                // Final report
                $this->command->info('Business structure hierarchy created successfully!');
                $this->command->info("Total nodes created: {$this->totalCreated}");
                foreach ($this->nodesByType as $type => $nodes) {
                    $count = count($nodes);
                    $this->command->info("{$type} nodes: {$count}");
                }

            } catch (Exception $e) {
                DB::rollBack();
                $this->command->error("Failed to seed: ".$e->getMessage());
                Log::error("Failed to seed: ".$e->getMessage()."\n".$e->getTraceAsString());
            }
        });
    }

    private function createCompanies($faker, $companyType)
    {
        $target = $this->targetCounts['Company'];
        $this->command->info("Creating {$target} Company nodes...");

        $progressBar = $this->command->getOutput()->createProgressBar($target);
        $progressBar->start();

        for ($i = 0; $i < $target; $i++) {
            $companyName = $faker->unique()->company;
            $company = BusinessStructureNode::create([
                'name' => $companyName,
                'description' => "{$companyName} - Top level organization",
                'business_structure_type_id' => $companyType->id,
                'parent_id' => null,
                'structure_hash' => md5($companyName.$companyType->id.now()->timestamp.$i),
                'path' => null,
                'path_hierarchy' => [],
                'start_date' => now(),
                'end_date' => now()->addYears(rand(3, 7)),
            ]);

            $this->nodesByType['Company'][] = $company;
            $this->totalCreated++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->info("\nCreated ".count($this->nodesByType['Company'])." company nodes");
    }

    private function createRegions($faker, $regionType)
    {
        $target = $this->targetCounts['Region'];
        $this->command->info("Creating {$target} Region nodes...");

        $progressBar = $this->command->getOutput()->createProgressBar($target);
        $progressBar->start();

        $companies = $this->nodesByType['Company'];
        $regionsCreated = 0;

        while ($regionsCreated < $target) {
            // Distribute regions evenly across companies
            foreach ($companies as $company) {
                if ($regionsCreated >= $target) {
                    break;
                }

                $regionName = $this->getRegionName($faker);
                $region = BusinessStructureNode::create([
                    'name' => $regionName,
                    'description' => "{$regionName} - Regional division",
                    'business_structure_type_id' => $regionType->id,
                    'parent_id' => $company->id,
                    'structure_hash' => md5($regionName.$company->id.$regionType->id.now()->timestamp.$regionsCreated),
                    'path' => (string) $company->id,
                    'path_hierarchy' => [$company->name],
                    'start_date' => now(),
                    'end_date' => now()->addYears(rand(3, 7)),
                ]);

                $this->nodesByType['Region'][] = $region;
                $regionsCreated++;
                $this->totalCreated++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->command->info("\nCreated ".count($this->nodesByType['Region'])." region nodes");
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

        return $faker->randomElement($regions).' '.$faker->randomNumber(2, true);
    }

    private function createStates($faker, $stateType)
    {
        $target = $this->targetCounts['State'];
        $this->command->info("Creating {$target} State nodes...");

        $progressBar = $this->command->getOutput()->createProgressBar($target);
        $progressBar->start();

        $regions = $this->nodesByType['Region'];
        $statesCreated = 0;

        while ($statesCreated < $target) {
            // Distribute states across regions
            foreach ($regions as $region) {
                if ($statesCreated >= $target) {
                    break;
                }

                $stateName = $faker->state;
                $state = BusinessStructureNode::create([
                    'name' => $stateName,
                    'description' => "{$stateName} - State division",
                    'business_structure_type_id' => $stateType->id,
                    'parent_id' => $region->id,
                    'structure_hash' => md5($stateName.$region->id.$stateType->id.now()->timestamp.$statesCreated),
                    'path' => $region->path.'.'.$region->id,
                    'path_hierarchy' => array_merge($region->path_hierarchy, [$region->name]),
                    'start_date' => now(),
                    'end_date' => now()->addYears(rand(3, 7)),
                ]);

                $this->nodesByType['State'][] = $state;
                $statesCreated++;
                $this->totalCreated++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->command->info("\nCreated ".count($this->nodesByType['State'])." state nodes");
    }

    private function createLocations($faker, $locationType)
    {
        $target = $this->targetCounts['Location'];
        $this->command->info("Creating {$target} Location nodes...");

        $progressBar = $this->command->getOutput()->createProgressBar($target);
        $progressBar->start();

        $states = $this->nodesByType['State'];
        $locationsCreated = 0;

        while ($locationsCreated < $target) {
            // Distribute locations across states
            foreach ($states as $state) {
                if ($locationsCreated >= $target) {
                    break;
                }

                $locationName = $faker->city." Location";
                $location = BusinessStructureNode::create([
                    'name' => $locationName,
                    'description' => "{$locationName} - Location division",
                    'business_structure_type_id' => $locationType->id,
                    'parent_id' => $state->id,
                    'structure_hash' => md5($locationName.$state->id.$locationType->id.now()->timestamp.$locationsCreated),
                    'path' => $state->path.'.'.$state->id,
                    'path_hierarchy' => array_merge($state->path_hierarchy, [$state->name]),
                    'start_date' => now(),
                    'end_date' => now()->addYears(rand(3, 7)),
                ]);

                $this->nodesByType['Location'][] = $location;
                $locationsCreated++;
                $this->totalCreated++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->command->info("\nCreated ".count($this->nodesByType['Location'])." location nodes");
    }

    private function createStores($faker, $storeType)
    {
        $target = $this->targetCounts['Store'];
        $this->command->info("Creating {$target} Store nodes...");

        $progressBar = $this->command->getOutput()->createProgressBar($target);
        $progressBar->start();

        $locations = $this->nodesByType['Location'];
        $storesCreated = 0;

        while ($storesCreated < $target) {
            // Distribute stores across locations
            foreach ($locations as $location) {
                if ($storesCreated >= $target) {
                    break;
                }

                $storeName = $faker->company." Store";
                $store = BusinessStructureNode::create([
                    'name' => $storeName,
                    'description' => "{$storeName} - Retail store",
                    'business_structure_type_id' => $storeType->id,
                    'parent_id' => $location->id,
                    'structure_hash' => md5($storeName.$location->id.$storeType->id.now()->timestamp.$storesCreated),
                    'path' => $location->path.'.'.$location->id,
                    'path_hierarchy' => array_merge($location->path_hierarchy, [$location->name]),
                    'start_date' => now(),
                    'end_date' => now()->addYears(rand(3, 7)),
                ]);

                $this->nodesByType['Store'][] = $store;
                $storesCreated++;
                $this->totalCreated++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->command->info("\nCreated ".count($this->nodesByType['Store'])." store nodes");
    }

    private function createDepartments($faker, $departmentType)
    {
        $target = $this->targetCounts['Department'];
        $this->command->info("Creating {$target} Department nodes...");

        $progressBar = $this->command->getOutput()->createProgressBar($target);
        $progressBar->start();

        $stores = $this->nodesByType['Store'];
        $departmentsCreated = 0;
        $batchSize = 100;
        $departments = [];

        while ($departmentsCreated < $target) {
            // Distribute departments across stores
            foreach ($stores as $store) {
                if ($departmentsCreated >= $target) {
                    break;
                }

                $departmentName = $this->getDepartmentName($faker);

                // Create the department directly for better handling of path_hierarchy
                $department = BusinessStructureNode::create([
                    'name' => $departmentName,
                    'description' => "{$departmentName} - Store department",
                    'business_structure_type_id' => $departmentType->id,
                    'parent_id' => $store->id,
                    'structure_hash' => md5($departmentName.$store->id.$departmentType->id.now()->timestamp.$departmentsCreated),
                    'path' => $store->path.'.'.$store->id,
                    'path_hierarchy' => array_merge($store->path_hierarchy, [$store->name]),
                    'start_date' => now(),
                    'end_date' => now()->addYears(rand(3, 7)),
                ]);

                $this->nodesByType['Department'][] = $department;
                $departmentsCreated++;
                $this->totalCreated++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->command->info("\nCreated {$departmentsCreated} department nodes");
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

        return $faker->randomElement($departments).' '.$faker->randomNumber(3, true);
    }

    private function createJobs($faker, $jobType)
    {
        $target = $this->targetCounts['Job'];
        $this->command->info("Creating {$target} Job nodes...");

        $progressBar = $this->command->getOutput()->createProgressBar($target);
        $progressBar->start();

        $departments = $this->nodesByType['Department'];
        $jobsCreated = 0;

        while ($jobsCreated < $target) {
            // Distribute jobs across departments
            foreach ($departments as $department) {
                if ($jobsCreated >= $target) {
                    break;
                }

                $jobName = $this->getJobName($faker, $department->name);

                $job = BusinessStructureNode::create([
                    'name' => $jobName,
                    'description' => "{$jobName} - Position in {$department->name}",
                    'business_structure_type_id' => $jobType->id,
                    'parent_id' => $department->id,
                    'structure_hash' => md5($jobName.$department->id.$jobType->id.now()->timestamp.$jobsCreated),
                    'path' => $department->path.'.'.$department->id,
                    'path_hierarchy' => array_merge($department->path_hierarchy, [$department->name]),
                    'start_date' => now(),
                    'end_date' => now()->addYears(rand(3, 7)),
                ]);

                $jobsCreated++;
                $this->totalCreated++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->command->info("\nCreated {$jobsCreated} job nodes");
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
