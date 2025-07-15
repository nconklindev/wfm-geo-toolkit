<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\CombinesMultipleApiCalls;
use App\Traits\ExportsCsvData;
use App\Traits\HandlesBatchedApiRequests;
use App\Traits\PaginatesApiData;
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
    use HandlesBatchedApiRequests;
    use PaginatesApiData;

    #[Validate('array')]
    public array $laborCategories = [];

    #[Validate('array')]
    public array $selectedLaborCategories = [];

    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view(
            'livewire.tools.api-explorer.endpoints.labor-categories-paginated-list',
            [
                'paginatedData' => $paginatedData,
            ],
        );
    }

    /**
     * Remove a category from the selected categories and refresh data
     */
    public function removeCategory(string $category): void
    {
        $this->selectedLaborCategories = array_values(
            array_filter(
                $this->selectedLaborCategories,
                static fn ($item) => $item !== $category,
            ),
        );
        $this->resetPage();
        $this->clearPaginationCache();
        $this->executeRequest();
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

        return $this->generateCsv(
            $filteredData->toArray(),
            $this->tableColumns,
            $filename,
        );
    }

    /**
     * Get all data for export (ignoring category filters)
     * TODO: This looks really similar to fetchAllRecordsWithSmartBatching
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

            $response = $this->makeAuthenticatedApiCall(
                function () use ($requestData) {
                    return $this->wfmService->getLaborCategoryEntriesPaginated(
                        $requestData,
                    );
                },
            );

            if (! $response || ! $response->successful()) {
                break;
            }

            $data = $response->json();
            $records = collect($data['records'] ?? []);

            if ($records->isEmpty()) {
                $hasMoreData = false;
            } else {
                // Transform data for export too
                $transformedRecords = $this->transformApiData(
                    $records->toArray(),
                );
                $allRecords = $allRecords->concat(
                    $transformedRecords->toArray(),
                );
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
     * Transform API data - convert boolean fields and clean up data structure
     */
    protected function transformApiData(array $data): Collection
    {
        // Don't transform boolean fields here - let the view handle the display
        // The view template expects actual boolean values to show icons and colors
        return collect($data);
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
            $categoryNames .= '-and-'.(count($this->selectedLaborCategories)
                    - 3).'-more';
        }

        $filename = 'labor-category-entries-'.$categoryNames.'_'.now()->format(
            'Y-m-d_H-i-s',
        );

        return $this->generateCsv(
            $filteredData->toArray(),
            $this->tableColumns,
            $filename,
        );
    }

    /**
     * Load all data based on selected categories
     */
    protected function loadDataBasedOnFilters(): void
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
                $this->fetchAllRecordsWithSmartBatching(
                    fn ($apiCall) => $this->makeAuthenticatedApiCall($apiCall),
                    fn ($requestData,
                    ) => $this->wfmService->getLaborCategoryEntriesPaginated(
                        $requestData,
                    ),
                    250,
                    1000,
                    'LaborCategoriesPaginatedList',
                );
            } else {
                // Load filtered data using multiple API calls
                $this->loadFilteredData();
            }

            $this->logPerformanceMetrics($startTime);
        } catch (ConnectionException $ce) {
            $this->errorMessage
                = 'Unable to connect to API. Please check your network connection and try again.';
            Log::error('Connection error in LaborCategoriesPaginatedList', [
                'error' => $ce->getMessage(),
                'selected_categories' => $this->selectedLaborCategories,
            ]);
            $this->totalRecords = 0;
            $this->cacheKey = '';
        }
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

        $combinedData = $this->makeMultipleApiCalls(
            $apiCallFunctions,
            function ($item) {
                return $item['id'] ?? ($item['name'] ?? '').'_'.data_get(
                    $item,
                    'laborCategory.name',
                    '',
                );
            },
        );

        // Transform the combined data
        $transformedData = $this->transformApiData($combinedData->toArray());

        // Store data in base class properties AND cache
        $this->storeDataInComponent($transformedData);

        Log::info('Combined Data Cached', [
            'component' => 'LaborCategoriesPaginatedList',
            'total_records_available' => $this->totalRecords,
            'selected_categories' => count($this->selectedLaborCategories),
            'cache_key' => $this->cacheKey,
            'memory_usage_mb' => round(
                memory_get_peak_usage(true) / 1024 / 1024,
                2,
            ),
        ]);
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

        return 'api_data_'.class_basename($this).'_'.hash(
            'sha256',
            $this->hostname.'_'.$userId.'_'.$sessionId.'_'.$categoriesHash,
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
            'data_source' => empty($this->selectedLaborCategories)
                ? 'api_all' : 'api_filtered',
            'cache_key' => $this->cacheKey,
        ]);
    }

    /**
     * Fetch data using smart batching and API calls, and return a formatted
     * response.
     *
     * This method utilizes smart batching to minimize the number of API calls
     * while efficiently fetching all relevant records. It processes the data
     * with respect to the selected labor categories, generates a response with
     * the records, and returns the total count of records.
     *
     * @return Response|null A formatted response containing the fetched
     *                       records and their count, or null if no data is retrieved.
     *
     * @throws \JsonException
     */
    protected function fetchData(): ?Response
    {
        // Use the smart batching trait to get all records
        $allRecords = $this->fetchAllRecordsWithSmartBatching(
            fn ($apiCall) => $this->makeAuthenticatedApiCall($apiCall),
            fn ($requestData,
            ) => $this->wfmService->getLaborCategoryEntriesPaginated(
                array_merge($requestData, [
                    'where' => [
                        'laborCategory' => [
                            'qualifier' => $this->selectedLaborCategories[0] ??
                                null,
                        ],
                    ],
                ]),
            ),
            250, // initial batch size
            1000, // max batch size
            'LaborCategoriesPaginatedList',
        );

        // If you need to handle multiple categories, you could do multiple calls
        // and combine them, or modify the batching method to handle this use case

        // Create a mock response object since we already have the processed data
        return new Response(
            new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'records' => $allRecords->toArray(),
                'total' => $allRecords->count(),
            ], JSON_THROW_ON_ERROR)),
        );
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
            $this->errorMessage
                = 'Unable to load labor categories. Please check your network connection and try again.';
            Log::error(
                'Failed to load labor categories in initializeEndpoint',
                [
                    'component' => get_class($this),
                    'hostname' => $this->hostname,
                    'response_status' => $response ? $response->status()
                        : 'no_response',
                    'response_body' => $response ? $response->body()
                        : 'no_response',
                ],
            );
        }
    }

    /**
     * Override to specify boolean fields for this endpoint
     */
    protected function getBooleanFields(): array
    {
        return ['inactive'];
    }
}
