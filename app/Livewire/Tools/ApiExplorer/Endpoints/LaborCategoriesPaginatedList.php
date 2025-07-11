<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\CombinesMultipleApiCalls;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaborCategoriesPaginatedList extends BaseApiEndpoint
{
    use CombinesMultipleApiCalls;
    use ExportsCsvData;
    use PaginatesApiData;

    #[Validate('array')]
    public array $laborCategories = [];

    #[Validate('array')]
    public array $selectedLaborCategories = [];

    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.labor-categories-paginated-list', [
            'paginatedData' => $paginatedData,
        ]);
    }

    /**
     * Remove a category from the selected categories and refresh data
     */
    public function removeCategory(string $category): void
    {
        $this->selectedLaborCategories = array_values(
            array_filter($this->selectedLaborCategories, fn ($item) => $item !== $category)
        );
        $this->resetPage();
        $this->clearPaginationCache();
        $this->executeRequest();
    }

    /**
     * Override executeRequest to handle custom filtering and clear raw JSON viewer
     */
    public function executeRequest(): void
    {
        // Clear raw JSON viewer when making new request
        $this->dispatch('clear-raw-json-viewer');

        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate first using the credentials form above.';

            return;
        }

        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            // Clear any existing cache
            $this->clearCache();

            // Load data based on selected categories
            $this->loadAllData();

            // Create user-friendly API response
            $this->apiResponse = [
                'status' => 200,
                'data' => [
                    'message' => "Data loaded successfully - {$this->totalRecords} records",
                    'record_count' => $this->totalRecords,
                    'click_to_view' => 'Click "Show Raw JSON" to view full response',
                    'cached' => false,
                ],
            ];

            // Set raw JSON cache key
            $this->rawJsonCacheKey = $this->cacheKey;

            // Trigger pagination refresh
            $this->clearPaginationCache();

        } catch (Exception $e) {
            $this->handleError($e);
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Load all data based on selected categories
     */
    protected function loadAllData(): void
    {
        if (! $this->isAuthenticated) {
            $this->totalRecords = 0;
            $this->cacheKey = '';

            return;
        }

        try {
            $startTime = microtime(true);

            if (empty($this->selectedLaborCategories)) {
                // Fetch ALL data in one API call
                $this->loadAllDataFromApi();
            } else {
                // Load filtered data using multiple API calls
                $this->loadFilteredData();
            }

            $this->logPerformanceMetrics($startTime);
        } catch (ConnectionException $ce) {
            $this->errorMessage = 'Unable to connect to API. Please check your network connection and try again.';
            Log::error('Connection error in LaborCategoriesPaginatedList', [
                'error' => $ce->getMessage(),
                'selected_categories' => $this->selectedLaborCategories,
            ]);
            $this->totalRecords = 0;
            $this->cacheKey = '';
        }
    }

    /**
     * Load all data from API using pagination
     */
    protected function loadAllDataFromApi(): void
    {
        // Start with a conservative batch size
        $batchSize = 250;
        $index = 0;
        $allRecords = collect();
        $hasMoreData = true;

        while ($hasMoreData) {
            $requestData = [
                'count' => $batchSize,
                'index' => $index,
            ];

            $response = $this->makeAuthenticatedApiCall(function () use ($requestData) {
                return $this->wfmService->getLaborCategoryEntriesPaginated($requestData);
            });

            if (! $response || ! $response->successful()) {
                // Handle API errors
                $this->errorMessage = 'Failed to load data from API. Please try again.';

                // Try with smaller batch size if we suspect limit issues
                if ($response && $response->status() === 400 && $batchSize > 100) {
                    $batchSize = 100;
                    Log::info('Retrying with smaller batch size', [
                        'new_batch_size' => $batchSize,
                        'original_batch_size' => $requestData['count'],
                    ]);

                    continue;
                }

                Log::error('API call failed in loadAllDataFromApi', [
                    'status' => $response ? $response->status() : 'no_response',
                    'requested_count' => $requestData['count'],
                    'hostname' => $this->hostname,
                ]);

                $this->totalRecords = 0;
                $this->cacheKey = '';

                return;
            }

            $data = $response->json();
            $records = collect($data['records'] ?? []);

            if ($records->isEmpty()) {
                $hasMoreData = false;
            } else {
                // Transform the data
                $transformedRecords = $this->transformApiData($records->toArray());
                $allRecords = $allRecords->concat($transformedRecords->toArray());
                $index += $batchSize;

                // If we got a full batch and haven't hit a limit, we can try increasing batch size
                if ($records->count() === $batchSize && $batchSize < 1000) {
                    $batchSize = min(1000, $batchSize * 2); // Double batch size up to 1000
                    Log::info('Increasing batch size', ['new_batch_size' => $batchSize]);
                }

                // Safety check: if we got fewer records than requested, we've reached the end
                if ($records->count() < $batchSize) {
                    $hasMoreData = false;
                }
            }
        }

        // Store data in base class properties AND cache
        $this->storeDataInComponent($allRecords);

        Log::info('All Data Loaded and Cached', [
            'component' => 'LaborCategoriesPaginatedList',
            'total_records' => $this->totalRecords,
            'final_batch_size' => $batchSize,
            'cache_key' => $this->cacheKey,
            'batches_processed' => ceil($index / $batchSize),
        ]);
    }

    /**
     * Transform API data - convert boolean fields and clean up data structure
     */
    protected function transformApiData(array $data): Collection
    {
        // Don't transform boolean fields here - let the view handle the display
        // The view template expects actual boolean values to show icons and colors
        return collect($data);
    }

    /**
     * Store data in both component properties and cache
     */
    protected function storeDataInComponent(Collection $data): void
    {
        // Convert collection to array for storage
        $dataArray = $data->toArray();

        // Store in base class properties (required for pagination trait)
        $this->tableData = $dataArray;
        $this->totalRecords = count($dataArray);

        // Generate cache key and store in cache
        $this->cacheKey = $this->generateCacheKey();
        cache()->put($this->cacheKey, $data, now()->addMinutes(30));

        // Clear pagination cache when new data is loaded
        $this->clearPaginationCache();
    }

    /**
     * Override the generateCacheKey method to include selected categories
     */
    protected function generateCacheKey(): string
    {
        $categoriesHash = empty($this->selectedLaborCategories)
            ? 'all'
            : hash('sha256', json_encode($this->getSortedCategories()));

        $userId = auth()->id() ?? 'anonymous';
        $sessionId = session()->getId();

        return 'api_data_'.class_basename($this).'_'.hash('sha256',
            $this->hostname.'_'.$userId.'_'.$sessionId.'_'.$categoriesHash
        );
    }

    /**
     * Get sorted categories for consistent cache key generation
     */
    private function getSortedCategories(): array
    {
        $categories = array_values($this->selectedLaborCategories);
        sort($categories);

        return $categories;
    }

    /**
     * Load filtered data using multiple API calls
     */
    protected function loadFilteredData(): void
    {
        $apiCallFunctions = [];

        foreach ($this->selectedLaborCategories as $category) {
            $apiCallFunctions[] = function () use ($category) {
                return $this->wfmService->getLaborCategoryEntriesPaginated([
                    'count' => 1000, // Respect the API limit
                    'index' => 0,
                    'where' => ['laborCategory' => ['qualifier' => $category]],
                ]);
            };
        }

        $combinedData = $this->makeMultipleApiCalls($apiCallFunctions, function ($item) {
            return $item['id'] ?? ($item['name'] ?? '').'_'.data_get($item, 'laborCategory.name', '');
        });

        // Transform the combined data
        $transformedData = $this->transformApiData($combinedData->toArray());

        // Store data in base class properties AND cache
        $this->storeDataInComponent($transformedData);

        Log::info('Combined Data Cached', [
            'component' => 'LaborCategoriesPaginatedList',
            'total_records_available' => $this->totalRecords,
            'selected_categories' => count($this->selectedLaborCategories),
            'cache_key' => $this->cacheKey,
            'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ]);
    }

    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics(float $startTime): void
    {
        $executionTime = (microtime(true) - $startTime) * 1000;
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;

        Log::info('LaborCategoriesPaginatedList Performance Metrics', [
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round($memoryUsage, 2),
            'total_records' => $this->totalRecords,
            'selected_categories' => count($this->selectedLaborCategories),
            'data_source' => empty($this->selectedLaborCategories) ? 'api_all' : 'api_filtered',
            'cache_key' => $this->cacheKey,
        ]);
    }

    /**
     * This component doesn't use the standard fetchData pattern due to complex filtering.
     * Instead, it uses a custom loadAllData() method
     */
    protected function fetchData(): ?Response
    {
        // This method is not used in this component
        // The component uses loadAllData() instead due to complex filtering logic
        return null;
    }

    /**
     * Initialize endpoint configuration
     */
    protected function initializeEndpoint(): void
    {
        // Configure the table structure
        $this->tableColumns = [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'description', 'label' => 'Description'],
            ['field' => 'inactive', 'label' => 'Inactive'],
            ['field' => 'laborCategory.name', 'label' => 'Labor Category'],
        ];

        // Initialize pagination with cache key
        $this->initializePaginationData();

        // Load available labor categories for the filter dropdown
        $this->loadLaborCategories();
    }

    /**
     * Load available labor categories for the filter dropdown
     */
    protected function loadLaborCategories(): void
    {
        if (! $this->isAuthenticated) {
            return;
        }

        $response = $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getLaborCategories();
        });

        if ($response && $response->successful()) {
            $data = $response->json();
            $this->laborCategories = array_column($data, 'name');
        } else {
            $this->errorMessage = 'Unable to load labor categories. Please check your network connection and try again.';
            Log::error('Failed to load labor categories in initializeEndpoint', [
                'component' => get_class($this),
                'hostname' => $this->hostname,
            ]);
        }
    }

    /**
     * Override the searchable fields for labor categories
     */
    protected function getSearchableFields(): array
    {
        return [
            'name',
            'description',
            'laborCategory.name',
        ];
    }

    /**
     * Export all labor category entries data as CSV
     */
    public function exportAllToCsv(): StreamedResponse
    {
        // Get ALL data regardless of category selections
        $allDataForExport = $this->getAllDataForExport();

        // Apply current search and sort to the full dataset
        $filteredData = $this->applyFiltersAndSort($allDataForExport);

        $filename = 'labor-category-entries-all_'.now()->format('Y-m-d_H-i-s');

        return $this->exportAsCsv($filteredData->toArray(), $this->tableColumns, $filename);
    }

    /**
     * Get all data for export (ignoring category filters)
     */
    protected function getAllDataForExport(): Collection
    {
        if (! $this->isAuthenticated) {
            return collect();
        }

        $batchSize = 250;
        $index = 0;
        $allRecords = collect();
        $hasMoreData = true;

        while ($hasMoreData) {
            $requestData = [
                'count' => $batchSize,
                'index' => $index,
            ];

            $response = $this->makeAuthenticatedApiCall(function () use ($requestData) {
                return $this->wfmService->getLaborCategoryEntriesPaginated($requestData);
            });

            if (! $response || ! $response->successful()) {
                break;
            }

            $data = $response->json();
            $records = collect($data['records'] ?? []);

            if ($records->isEmpty()) {
                $hasMoreData = false;
            } else {
                // Transform data for export too
                $transformedRecords = $this->transformApiData($records->toArray());
                $allRecords = $allRecords->concat($transformedRecords->toArray());
                $index += $batchSize;

                // If the count is less than the batch size, then we've reached the end
                if ($records->count() < $batchSize) {
                    $hasMoreData = false;
                }
            }
        }

        return $allRecords;
    }

    /**
     * Export current page/filtered data as CSV
     */
    public function exportSelectionsToCsv(): StreamedResponse
    {
        // Get the current filtered and sorted data (what the user is seeing)
        $exportData = $this->getAllData();
        $filteredData = $this->applyFiltersAndSort($exportData);

        $categoryNames = empty($this->selectedLaborCategories)
            ? 'current-view'
            : implode('-', array_slice($this->selectedLaborCategories, 0, 3));

        if (count($this->selectedLaborCategories) > 3) {
            $categoryNames .= '-and-'.(count($this->selectedLaborCategories) - 3).'-more';
        }

        $filename = 'labor-category-entries-'.$categoryNames.'_'.now()->format('Y-m-d_H-i-s');

        return $this->exportAsCsv($filteredData->toArray(), $this->tableColumns, $filename);
    }

    /**
     * Override to specify boolean fields for this endpoint
     */
    protected function getBooleanFields(): array
    {
        return ['inactive'];
    }
}
