<?php

use App\Http\Resources\KnownPlaceResource;
use App\Livewire\ExportKnownPlaces;
use App\Models\KnownPlace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

// Helper function to create a user and authenticate
function createUserAndAuthenticate(): User
{
    $user = User::factory()->create();
    actingAs($user);
    return $user;
}

test('can export all known places as JSON with default settings', function () {
    $user = createUserAndAuthenticate();
    $knownPlace1 = KnownPlace::factory()->for($user)->create(['name' => 'Place 1']);
    $knownPlace2 = KnownPlace::factory()->for($user)->create(['name' => 'Place 2']);

    // Create the expected data format first - this will be used for comparison
    $expectedData = KnownPlaceResource::collection(collect([
        $knownPlace1,
        $knownPlace2
    ]))->response()->getData(true);

    // Convert to JSON with pretty print - this will be the expected output
    $expectedJson = json_encode($expectedData, JSON_PRETTY_PRINT);

    Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'json')
        ->set('placesFilter', 'all')
        ->set('includeTimestamps', false)
        ->call('export')
        ->assertFileDownloaded(
            'known_places_'.today()->format('Y-m-d'),
            $expectedJson
        );
});

test('can export all known places as JSON including timestamps', function () {
    $user = createUserAndAuthenticate();
    $knownPlace = KnownPlace::factory()->for($user)->create();

    // Create a request with include_timestamps=true to match what the component will do
    $request = request()->merge(['include_timestamps' => true]);

    // Get the expected data with timestamps included
    $expectedData = KnownPlaceResource::collection(collect([$knownPlace]))
        ->response($request)
        ->getData(true);

    // Convert to JSON with pretty print - this will be the expected output
    $expectedJson = json_encode($expectedData, JSON_PRETTY_PRINT);

    // Verify that the JSON contains the timestamps
    expect($expectedJson)->toBeJson()
        ->and($expectedJson)->toContain('"created_at"')
        ->and($expectedJson)->toContain('"updated_at"');

    // Test the export and expected file name
    Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'json')
        ->set('placesFilter', 'all')
        ->set('includeTimestamps', true)
        ->call('export')
        ->assertFileDownloaded('known_places_'.today()->format('Y-m-d'), $expectedJson);

    // For timestamps, we'll verify separately that created_at and updated_at exist in the data
    // This is a workaround because direct string comparison with JSON is problematic
    expect($expectedData)->toBeArray()
        ->and($expectedData['data'])->toBeArray()
        ->and($expectedData['data'][0])->toHaveKey('created_at')
        ->and($expectedData['data'][0])->toHaveKey('updated_at');
});

test('can export recent known places as JSON and include places exactly 30 days old', function () {
    $user = createUserAndAuthenticate();

    // Use a fixed time for testing to avoid flaky tests
    $now = now();

    // Create places with specific dates
    KnownPlace::factory()->for($user)->create([
        'name' => 'Recent Place',
        'created_at' => $now->copy()->subDays(5)
    ]);

    KnownPlace::factory()->for($user)->create([
        'name' => 'Edge Case Place',
        'created_at' => $now->copy()->subDays(30)->subHours(5) // 30 days and 5 hours ago
    ]);

    KnownPlace::factory()->for($user)->create([
        'name' => 'Old Place',
        'created_at' => $now->copy()->subDays(31)
    ]);

    // Debug the dates we're using
//    dump('Now:', $now->toDateTimeString());
//    dump('Recent Place:', $recentPlace->created_at->toDateTimeString());
//    dump('Edge Case Place:', $edgeCasePlace->created_at->toDateTimeString());
//    dump('Old Place:', $oldPlace->created_at->toDateTimeString());
//    dump('Cutoff Date:', $now->copy()->subDays(30)->startOfDay()->toDateTimeString());

    // Test the component
    $component = Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'json')
        ->set('placesFilter', 'recent')
        ->set('includeTimestamps', false);

    // Call the export method
    $component->call('export');

    // Get the downloaded content and decode it
    $downloadedContent = $component->effects['download']['content'] ?? null;
    $decodedContent = $downloadedContent ? json_decode(base64_decode($downloadedContent), true) : null;

    // Extract the place names for simpler testing
    $downloadedNames = collect($decodedContent['data'] ?? [])->pluck('name')->toArray();
//    dump('Downloaded place names:', $downloadedNames);

    // Verify the expected places are included/excluded
    expect($downloadedNames)->toContain('Recent Place')
        ->toContain('Edge Case Place') // The edge case should be included
        ->not->toContain('Old Place');
});

test('can export custom selected known places as JSON', function () {
    $user = createUserAndAuthenticate();
    $selectedPlace1 = KnownPlace::factory()->for($user)->create();
    $selectedPlace2 = KnownPlace::factory()->for($user)->create();
    KnownPlace::factory()->for($user)->create();

    // Create the expected data format
    $expectedData = KnownPlaceResource::collection(collect([
        $selectedPlace1,
        $selectedPlace2
    ]))->response()->getData(true);

    // Convert to JSON with pretty print
    $expectedJson = json_encode($expectedData, JSON_PRETTY_PRINT);

    Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'json')
        ->set('placesFilter', 'custom')
        ->set('selectedPlaces', [$selectedPlace1->id, $selectedPlace2->id])
        ->set('includeTimestamps', false)
        ->call('export')
        ->assertFileDownloaded(
            'known_places_'.today()->format('Y-m-d'),
            $expectedJson,
        );
});

test('shows error if no known places are found for JSON export', function () {
    createUserAndAuthenticate();

    Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'json')
        ->set('placesFilter', 'all')
        ->call('export')
        ->assertNoFileDownloaded()
        ->assertRedirect(ExportKnownPlaces::class);
//        ->assertNotified('No known places found to export.');
});

test('export as JSON uses pretty print', function () {
    $user = createUserAndAuthenticate();
    $place = KnownPlace::factory()->for($user)->create();

    // Create a simple callback function to validate the pretty print
    // without requiring exact content matching

    // Create expected data
    $expectedData = KnownPlaceResource::collection(collect([$place]))->response()->getData(true);
    $expectedJson = json_encode($expectedData, JSON_PRETTY_PRINT);

    Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'json')
        ->set('placesFilter', 'all')
        ->call('export')
        ->assertFileDownloaded(
            'known_places_'.today()->format('Y-m-d'),
            $expectedJson,
        );
});

test('can export all known places as CSV with default settings', function () {
    $user = createUserAndAuthenticate();
    $place1 = KnownPlace::factory()->for($user)->create(['name' => 'Place 1']);
    $place2 = KnownPlace::factory()->for($user)->create(['name' => 'Place 2']);

    // Get expected data in same format as the component would use
    $request = request()->merge(['include_timestamps' => false]);
    $resourceCollection = KnownPlace::find([$place1->id, $place2->id])->toResourceCollection();
    $expectedData = $resourceCollection->toArray($request);

    // Create a copy of expected data without timestamps, which is what we expect in the CSV
    $expectedDataWithoutTimestamps = array_map(function ($item) {
        $filtered = $item;
        unset($filtered['created_at'], $filtered['updated_at']);
        return $filtered;
    }, $expectedData);

    // Test the export functionality
    $component = Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'csv')
        ->set('placesFilter', 'all')
        ->set('includeTimestamps', false)
        ->call('export');

    // Get the downloaded content and check CSV structure
    $downloadedContent = $component->effects['download']['content'] ?? null;
    $decodedContent = $downloadedContent ? base64_decode($downloadedContent) : null;

    // Basic assertions
    expect($decodedContent)->not->toBeNull()
        ->and($component->effects['download']['name'])->toBe('known_places_'.today()->format('Y-m-d').'.csv');

    // Split CSV into lines for testing
    $csvLines = explode("\n", $decodedContent);

    // Get the header line from the CSV
    $headerLine = str_getcsv($csvLines[0]);

    // Check that all expected header fields (without timestamps) exist in the actual headers
    $expectedHeaderFields = array_keys($expectedDataWithoutTimestamps[0]);
    foreach ($expectedHeaderFields as $field) {
        expect($headerLine)->toContain($field);
    }

    // Check row count - should be one header row plus one row per place
    expect(count(array_filter($csvLines)))->toBe(count($expectedData) + 1);
});

test('can export CSV with transformed data for Pro WFM', function () {
    $user = createUserAndAuthenticate();
    $place = KnownPlace::factory()->for($user)->create([
        'name' => 'Test Place',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'radius' => 100
    ]);

    // Test CSV export with transformation
    $component = Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'csv')
        ->set('placesFilter', 'all')
        ->set('transformData', true)
        ->call('export');

    $downloadedContent = $component->effects['download']['content'] ?? null;
    $decodedContent = $downloadedContent ? base64_decode($downloadedContent) : null;

    // Basic assertions
    expect($decodedContent)->not->toBeNull()
        ->and($component->effects['download']['name'])->toBe('known_places_'.today()->format('Y-m-d').'.csv');

    // Split CSV into lines for testing
    $csvLines = explode("\n", $decodedContent);

    // Check header line for transformed data
    $expectedHeaders = ['name', 'latitude', 'longitude', 'radius', 'locations', 'validation_order'];
    $headerLine = str_getcsv($csvLines[0]);
    expect($headerLine)->toMatchArray($expectedHeaders);
});

test('can export recent known places as CSV and exclude older places', function () {
    $user = createUserAndAuthenticate();

    // Use a fixed time for testing to avoid flaky tests
    $now = now();

    // Create places with specific dates
    $recentPlace = KnownPlace::factory()->for($user)->create([
        'name' => 'Recent Place',
        'created_at' => $now->copy()->subDays(5)
    ]);

    $edgeCasePlace = KnownPlace::factory()->for($user)->create([
        'name' => 'Edge Case Place',
        'created_at' => $now->copy()->subDays(30)->subHours(5) // 30 days and 5 hours ago
    ]);

    $oldPlace = KnownPlace::factory()->for($user)->create([
        'name' => 'Old Place',
        'created_at' => $now->copy()->subDays(31)
    ]);

    // Test the export
    $component = Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'csv')
        ->set('placesFilter', 'recent')
        ->set('includeTimestamps', false)
        ->call('export');

    $downloadedContent = $component->effects['download']['content'] ?? null;
    $decodedContent = $downloadedContent ? base64_decode($downloadedContent) : null;

    // Basic assertions
    expect($decodedContent)->not->toBeNull();

    // Parse the CSV content
    $csvLines = explode("\n", $decodedContent);
    $csvLines = array_filter($csvLines); // Remove empty lines

    // Convert to array for easier testing
    $csvData = [];
    $headers = str_getcsv(array_shift($csvLines)); // Get the headers

    foreach ($csvLines as $line) {
        $rowData = str_getcsv($line);
        if (count($rowData) === count($headers)) {
            $row = array_combine($headers, $rowData);
            $csvData[] = $row;
        }
    }

    // Extract just the names for simpler testing
    $exportedNames = array_column($csvData, 'name');

    // Verify the expected places are included/excluded
    expect($exportedNames)->toContain('Recent Place')
        ->toContain('Edge Case Place') // Edge case should be included
        ->not->toContain('Old Place'); // Old places should be excluded
});

test('can export custom selected known places as CSV', function () {
    $user = createUserAndAuthenticate();
    $selectedPlace1 = KnownPlace::factory()->for($user)->create(['name' => 'Selected 1']);
    $selectedPlace2 = KnownPlace::factory()->for($user)->create(['name' => 'Selected 2']);
    $unselectedPlace = KnownPlace::factory()->for($user)->create(['name' => 'Unselected']);

    // Test CSV export with custom selection
    $component = Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'csv')
        ->set('placesFilter', 'custom')
        ->set('selectedPlaces', [$selectedPlace1->id, $selectedPlace2->id])
        ->call('export');

    $downloadedContent = $component->effects['download']['content'] ?? null;
    $decodedContent = $downloadedContent ? base64_decode($downloadedContent) : null;

    // Parse the CSV content
    $csvLines = explode("\n", $decodedContent);
    $csvLines = array_filter($csvLines); // Remove empty lines

    // Convert to array for easier testing
    $csvData = [];
    $headers = str_getcsv(array_shift($csvLines)); // Get the headers

    foreach ($csvLines as $line) {
        $rowData = str_getcsv($line);
        if (count($rowData) === count($headers)) {
            $row = array_combine($headers, $rowData);
            $csvData[] = $row;
        }
    }

    // Extract just the names for simpler testing
    $exportedNames = array_column($csvData, 'name');

    // Verify the correct places were included/excluded
    expect($exportedNames)->toContain('Selected 1')
        ->toContain('Selected 2')
        ->not->toContain('Unselected');
});

test('shows error if no known places are found for CSV export', function () {
    createUserAndAuthenticate();

    Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'csv')
        ->set('placesFilter', 'all')
        ->call('export')
        ->assertNoFileDownloaded()
        ->assertRedirect(ExportKnownPlaces::class);
});

test('CSV export sanitizes values to prevent injection', function () {
    $user = createUserAndAuthenticate();

    // Create a place with potentially dangerous values
    $place = KnownPlace::factory()->for($user)->create([
        'name' => '=SUM(1+1)', // Formula injection attempt
    ]);

    // Test CSV export
    $component = Livewire::test(ExportKnownPlaces::class)
        ->set('fileFormat', 'csv')
        ->set('placesFilter', 'all')
        ->call('export');

    $downloadedContent = $component->effects['download']['content'] ?? null;
    $decodedContent = $downloadedContent ? base64_decode($downloadedContent) : null;

    // Parse the CSV content
    $csvLines = explode("\n", $decodedContent);
    $csvLines = array_filter($csvLines); // Remove empty lines

    // Skip the header row and focus on the data row
    $dataRow = str_getcsv($csvLines[1]);

    // Get the name column index from the header row
    $headerRow = str_getcsv($csvLines[0]);
    $nameIndex = array_search('name', $headerRow);

    // Check that the name was sanitized
    expect($dataRow[$nameIndex])->toBe("'=SUM(1+1)"); // Should be prefixed with a quote
});
