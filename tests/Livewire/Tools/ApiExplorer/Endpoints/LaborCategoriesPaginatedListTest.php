<?php

use App\Livewire\Tools\ApiExplorer\Endpoints\LaborCategoriesPaginatedList;
use App\Services\WfmService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

uses(Tests\TestCase::class);

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();

    // Set up session with authentication
    session([
        'wfm_authenticated' => true,
        'wfm_access_token' => 'test-token',
        'wfm_credentials.hostname' => 'test-hostname',
    ]);
});

afterEach(function () {
    Mockery::close();
});

// Helper function to create a properly mocked response
function createMockResponse($data = [], $successful = true, $status = 200): Mockery\MockInterface
{
    $mockResponse = Mockery::mock(Response::class);
    $mockResponse->shouldReceive('successful')->andReturn($successful);
    $mockResponse->shouldReceive('status')->andReturn($status);
    $mockResponse->shouldReceive('headers')->andReturn([]);
    $mockResponse->shouldReceive('json')->andReturn($data);

    return $mockResponse;
}

test('component initializes correctly with authentication', function () {
    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken')->with('test-token');
    $mockWfmService->shouldReceive('setHostname')->with('test-hostname');

    $mockResponse = createMockResponse([
        ['name' => 'Category 1'],
        ['name' => 'Category 2'],
    ]);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($mockResponse);

    $this->app->instance(WfmService::class, $mockWfmService);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    $component->assertSet('isAuthenticated', true);
    $component->assertSet('hostname', 'test-hostname');
    $component->assertSet('laborCategories', ['Category 1', 'Category 2']);
    $component->assertSet('tableColumns', [
        ['field' => 'name', 'label' => 'Name'],
        ['field' => 'description', 'label' => 'Description'],
        ['field' => 'inactive', 'label' => 'Inactive'],
        ['field' => 'laborCategory.name', 'label' => 'Labor Category'],
    ]);
});

test('component handles unauthenticated state', function () {
    // Clear authentication session
    session()->forget(['wfm_authenticated', 'wfm_access_token']);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    $component->assertSet('isAuthenticated', false);
    $component->assertSet('laborCategories', []);
    $component->assertSet('totalRecords', 0);
});

test('removeCategory removes category from selected categories', function () {
    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken');
    $mockWfmService->shouldReceive('setHostname');

    $mockResponse = createMockResponse([]);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($mockResponse);
    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')->andReturn($mockResponse);

    $this->app->instance(WfmService::class, $mockWfmService);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    $component->set('selectedLaborCategories', ['Category 1', 'Category 2', 'Category 3']);
    $component->call('removeCategory', 'Category 2');

    $component->assertSet('selectedLaborCategories', ['Category 1', 'Category 3']);
});

test('removeCategory calls loadAllData when no categories selected', function () {
    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken');
    $mockWfmService->shouldReceive('setHostname');

    $initResponse = createMockResponse([]);
    $dataResponse = createMockResponse([
        'records' => [
            ['id' => 1, 'name' => 'Entry 1'],
            ['id' => 2, 'name' => 'Entry 2'],
        ],
    ]);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($initResponse);
    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')
        ->with([
            'count' => 50000,
            'index' => 0,
        ])
        ->andReturn($dataResponse);

    $this->app->instance(WfmService::class, $mockWfmService);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    $component->set('selectedLaborCategories', ['Category 1']);
    $component->call('removeCategory', 'Category 1'); // This should trigger loadAllData

    $component->assertSet('selectedLaborCategories', []);
    $component->assertSet('totalRecords', 2);
});

test('removeCategory calls loadAllData when categories selected', function () {
    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken');
    $mockWfmService->shouldReceive('setHostname');

    $initResponse = createMockResponse([]);
    $dataResponse = createMockResponse([
        'records' => [
            ['id' => 1, 'name' => 'Entry 1'],
        ],
    ]);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($initResponse);
    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')
        ->with([
            'count' => 10000,
            'index' => 0,
            'where' => ['laborCategory' => ['qualifier' => 'Category 1']],
        ])
        ->andReturn($dataResponse);

    $this->app->instance(WfmService::class, $mockWfmService);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    $component->set('selectedLaborCategories', ['Category 1', 'Category 2']);
    $component->call('removeCategory', 'Category 2'); // This should trigger loadAllData with filtered data

    $component->assertSet('selectedLaborCategories', ['Category 1']);
    $component->assertSet('totalRecords', 1);
});

test('executeRequest triggers data loading through makeApiCall', function () {
    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken');
    $mockWfmService->shouldReceive('setHostname');

    $initResponse = createMockResponse([]);
    $dataResponse = createMockResponse([
        'records' => [
            ['id' => 1, 'name' => 'Entry 1'],
        ],
    ]);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($initResponse);
    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')
        ->with([
            'count' => 50000,
            'index' => 0,
        ])
        ->andReturn($dataResponse);

    $this->app->instance(WfmService::class, $mockWfmService);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    $component->set('selectedLaborCategories', []);
    $component->call('executeRequest')
        ->assertDispatched('clear-raw-json-viewer');

    $component->assertSet('totalRecords', 1);
});

test('component handles connection exception during removeCategory', function () {
    Log::shouldReceive('error')->once();
    Log::shouldReceive('info')->once();

    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken');
    $mockWfmService->shouldReceive('setHostname');

    $mockResponse = createMockResponse([]);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($mockResponse);
    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')
        ->andThrow(new ConnectionException('Connection failed'));

    $this->app->instance(WfmService::class, $mockWfmService);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    $component->set('selectedLaborCategories', ['Category 1']);
    $component->call('removeCategory', 'Category 1');

    $component->assertSet(
        'errorMessage',
        'Unable to connect to API. Please check your network connection and try again.'
    );
    $component->assertSet('totalRecords', 0);
    $component->assertSet('cacheKey', '');
});

test('component handles unauthenticated state during removeCategory', function () {
    // Clear authentication session
    session()->forget(['wfm_authenticated', 'wfm_access_token']);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    $component->set('selectedLaborCategories', ['Category 1']);
    $component->call('removeCategory', 'Category 1');

    $component->assertSet('selectedLaborCategories', []);
    $component->assertSet('totalRecords', 0);
    $component->assertSet('cacheKey', '');
});

test('component handles large dataset performance', function () {
    // Mock Log facade to capture both the data caching and performance metrics
    Log::shouldReceive('info')->with('Data Cached', Mockery::on(function ($data) {
        return $data['component'] === 'LaborCategoriesPaginatedList' &&
               isset($data['total_records_available']) &&
               isset($data['records_fetched']) &&
               isset($data['cache_key']) &&
               isset($data['memory_usage_mb']);
    }))->once();

    Log::shouldReceive('info')->with(
        'LaborCategoriesPaginatedList Performance Metrics',
        Mockery::on(function ($data) {
            // Verify performance metrics are logged with expected structure
            return isset($data['execution_time_ms']) &&
                   isset($data['memory_usage_mb']) &&
                   isset($data['total_records']) &&
                   $data['total_records'] === 10000 && // Expected record count
                   isset($data['data_source']) &&
                   $data['data_source'] === 'api_all';
        })
    )->once();

    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken');
    $mockWfmService->shouldReceive('setHostname');

    // Create a large dataset (simulating 10,000 records)
    $largeDataset = [];
    for ($i = 1; $i <= 10000; $i++) {
        $largeDataset[] = [
            'id' => $i,
            'name' => "Entry {$i}",
            'description' => "Description for entry {$i}",
            'inactive' => $i % 2 === 0, // Mix of active/inactive
            'laborCategory' => [
                'name' => 'Category '.(($i % 5) + 1), // 5 different categories
            ],
        ];
    }

    $initResponse = createMockResponse([]);
    $dataResponse = createMockResponse([
        'records' => $largeDataset,
    ]);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($initResponse);
    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')
        ->with([
            'count' => 50000,
            'index' => 0,
        ])
        ->andReturn($dataResponse);

    $this->app->instance(WfmService::class, $mockWfmService);

    // Measure execution time
    $startTime = microtime(true);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    // This will trigger loadAllData() which calls logPerformanceMetrics()
    $component->set('selectedLaborCategories', []);
    $component->call('removeCategory', 'NonExistentCategory'); // This will trigger loadAllData

    $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

    // Verify the component handled the large dataset
    $component->assertSet('totalRecords', 10000);

    // Performance assertions - adjust thresholds as needed
    expect($executionTime)->toBeLessThan(5000); // Should complete within 5 seconds

    // Verify memory usage is reasonable (check current memory usage)
    $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB
    expect($memoryUsage)->toBeLessThan(512); // Should use less than 512MB
});

test('component handles large filtered dataset with multiple categories', function () {
    // Mock Log facade - expecting performance metrics + filtered data logging
    Log::shouldReceive('info')->twice();

    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken');
    $mockWfmService->shouldReceive('setHostname');

    // Create datasets for 3 different categories (3000 records each)
    $createCategoryData = function ($categoryName, $startId, $count) {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $id = $startId + $i;
            $data[] = [
                'id' => $id,
                'name' => "Entry {$id}",
                'description' => "Description for entry {$id}",
                'inactive' => $i % 2 === 0,
                'laborCategory' => ['name' => $categoryName],
            ];
        }

        return $data;
    };

    $initResponse = createMockResponse([]);

    // Mock responses for each category
    $category1Data = $createCategoryData('Category 1', 1, 3000);
    $category2Data = $createCategoryData('Category 2', 3001, 3000);
    $category3Data = $createCategoryData('Category 3', 6001, 3000);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($initResponse);

    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')
        ->with([
            'count' => 10000,
            'index' => 0,
            'where' => ['laborCategory' => ['qualifier' => 'Category 1']],
        ])
        ->andReturn(createMockResponse(['records' => $category1Data]));

    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')
        ->with([
            'count' => 10000,
            'index' => 0,
            'where' => ['laborCategory' => ['qualifier' => 'Category 2']],
        ])
        ->andReturn(createMockResponse(['records' => $category2Data]));

    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')
        ->with([
            'count' => 10000,
            'index' => 0,
            'where' => ['laborCategory' => ['qualifier' => 'Category 3']],
        ])
        ->andReturn(createMockResponse(['records' => $category3Data]));

    $this->app->instance(WfmService::class, $mockWfmService);

    // Measure execution time for filtered data
    $startTime = microtime(true);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    // Set categories first, then trigger loadAllData
    $component->set('selectedLaborCategories', ['Category 1', 'Category 2', 'Category 3']);
    $component->call('removeCategory', 'NonExistentCategory'); // This will trigger loadAllData with filtered data

    $executionTime = (microtime(true) - $startTime) * 1000;

    // Verify the component combined and deduplicated the data correctly
    $component->assertSet('totalRecords', 9000); // 3000 * 3 categories

    // Performance assertions for filtered data (might be slower due to multiple API calls)
    expect($executionTime)->toBeLessThan(7000); // Should complete within 7 seconds

    // Memory usage should still be reasonable
    $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;
    expect($memoryUsage)->toBeLessThan(768); // Slightly higher limit for filtered data
});

test('component pagination works efficiently with large dataset', function () {
    // Mock Log facade - allow the data caching log
    Log::shouldReceive('info')->with('Data Cached', Mockery::any())->once();
    Log::shouldReceive('info')->with('LaborCategoriesPaginatedList Performance Metrics', Mockery::any())->once();

    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken');
    $mockWfmService->shouldReceive('setHostname');

    // Create a large dataset
    $largeDataset = [];
    for ($i = 1; $i <= 5000; $i++) {
        $largeDataset[] = [
            'id' => $i,
            'name' => "Entry {$i}",
            'description' => "Description for entry {$i}",
            'inactive' => false,
            'laborCategory' => ['name' => 'Category 1'],
        ];
    }

    $initResponse = createMockResponse([]);
    $dataResponse = createMockResponse(['records' => $largeDataset]);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($initResponse);
    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')->andReturn($dataResponse);

    $this->app->instance(WfmService::class, $mockWfmService);

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    // Load the data first
    $component->set('selectedLaborCategories', []);
    $component->call('removeCategory', 'NonExistentCategory'); // This loads the data

    // Test pagination performance by accessing the view
    $startTime = microtime(true);

    // Test the component renders correctly with pagination
    $component->assertOk();

    $paginationTime = (microtime(true) - $startTime) * 1000;

    // Verify pagination is working
    $component->assertSet('totalRecords', 5000);

    // Pagination should be reasonably fast
    expect($paginationTime)->toBeLessThan(1000); // Should complete within 1 second

    // Test changing page by setting query string parameter
    $startTime = microtime(true);

    // Simulate page change through query parameters (how Livewire pagination works)
    request()->merge(['page' => 10]);
    $component->assertOk(); // This will trigger pagination internally

    $jumpTime = (microtime(true) - $startTime) * 1000;

    expect($jumpTime)->toBeLessThan(1000); // Page jumping should also be reasonably fast
});

test('component handles memory efficiently with very large dataset', function () {
    // Mock Log facade - allow the data caching log
    Log::shouldReceive('info')->with('Data Cached', Mockery::any())->once();
    Log::shouldReceive('info')->with('LaborCategoriesPaginatedList Performance Metrics', Mockery::any())->once();

    $mockWfmService = Mockery::mock(WfmService::class);
    $mockWfmService->shouldReceive('setAccessToken');
    $mockWfmService->shouldReceive('setHostname');

    // Create a very large dataset to test memory efficiency
    $veryLargeDataset = [];
    for ($i = 1; $i <= 100000; $i++) { // 25k records
        $veryLargeDataset[] = [
            'id' => $i,
            'name' => "Entry {$i}",
            'description' => "This is a longer description for entry {$i} to test memory usage with more realistic data sizes",
            'inactive' => $i % 3 === 0,
            'laborCategory' => ['name' => 'Category '.(($i % 10) + 1)],
        ];
    }
    echo "Done! Dataset successfully created\n";

    $initResponse = createMockResponse([]);
    $dataResponse = createMockResponse(['records' => $veryLargeDataset]);

    $mockWfmService->shouldReceive('getLaborCategories')->andReturn($initResponse);
    $mockWfmService->shouldReceive('getLaborCategoryEntriesPaginated')->andReturn($dataResponse);

    $this->app->instance(WfmService::class, $mockWfmService);

    // Measure memory before
    $memoryBefore = memory_get_usage(true);
    echo "\033[31mMemory before: {$memoryBefore} bytes\033[0m\n";

    $component = Livewire::test(LaborCategoriesPaginatedList::class);

    // Load the large dataset
    $component->set('selectedLaborCategories', []);
    $component->call('removeCategory', 'NonExistentCategory');

    // Measure memory after
    $memoryAfter = memory_get_usage(true);
    $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // Convert to MB
    echo "\033[32mMemory after: {$memoryAfter} bytes\033[0m\n";
    echo "\033[32mMemory used: {$memoryUsed} MB\033[0m\n";

    // Verify the component handled the large dataset
    $component->assertSet('totalRecords', 100000);

    // Test that the component can handle the large dataset without errors
    $component->assertOk();

    // Memory usage should be reasonable even with large dataset
    expect($memoryUsed)->toBeLessThan(256); // Should use less than 256MB additional memory

    // Peak memory usage should also be reasonable
    $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
    expect($peakMemory)->toBeLessThan(1024); // Should stay under 1GB peak
});
